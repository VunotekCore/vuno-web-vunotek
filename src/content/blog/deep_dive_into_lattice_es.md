---
title: 'Deep Dive into Lattice: Describing Inertia UIs in Pure PHP'
excerpt: 'Explore Lattice, a server-driven UI framework for Laravel that lets you build feature-rich React interfaces via Inertia using structured PHP classes.'
date: 2026-06-17
category: Laravel
author: Paul Redmond
locale: en
image: blog/lattice-php-inertia
---

Lattice is an innovative server-driven UI framework built for Laravel that allows developers to completely define a screen's layout—including pages, forms, data tables, navigation menus, and triggers—using elegant PHP code while rendering them natively as React components over Inertia.js. With this design pattern, the backend serves as the single source of truth for the application's visual architecture, keeping the frontend responsibilities scoped purely to rendering the state payload.

Under the hood, a page is represented by a dedicated PHP class responsible for composing a component definition tree. Lattice effortlessly serializes this architecture into a typed JSON payload and sends it across the wire as a standard Inertia page visit. A universal React wrapper on the client side then resolves each structured node against an established registry to draw the layout accurately.

## Key Benefits of the Lattice Model

- **Class-Driven Automated Routing:** By declaring the `#[AsPage]` attribute on a layout class, the framework registers routes dynamically. Lattice automatically scans your directory paths and wires up endpoints without requiring manual declarations in your routing configuration files.
- **Form Architectures Backed by Laravel Rules:** Input fields are defined entirely in backend code, evaluated using native Laravel validation, and easily enhanced with async inline evaluations powered by Laravel Precognition.
- **Eloquent-Powered Data Tables:** Complex data interaction patterns—such as column ordering, keyword filtering, and dataset pagination—are handled transparently by attaching custom table classes to an Eloquent query builder instance.
- **Server Actions with Responsive Client Effects:** Triggering a UI event executes a PHP handler on the server. Instead of returning full layout views, it yields specific user instructions (_effects_) that the client executes immediately, such as displaying toast notices, triggering partial layout hot-reloads, or executing path redirects.

## Declaring Pages

A structural page extends the foundational `Page` class, carries the route mapping via the `#[AsPage]` attribute, and organizes its user interface inside the `render()` method. The structural component layout is put together using a clean object-oriented PHP interface featuring components like `Stack`, `Grid`, `Heading`, and `Card` rather than writing JSX or raw Blade templates:

```typescript
use Lattice\Lattice\Attributes\AsPage;
use Lattice\Lattice\Core\Components\Card;
use Lattice\Lattice\Core\Components\Grid;
use Lattice\Lattice\Core\Components\Heading;
use Lattice\Lattice\Core\Components\Stack;
use Lattice\Lattice\Core\Components\Text;
use Lattice\Lattice\Core\Enums\Gap;
use Lattice\Lattice\Core\PageSchema;
use Lattice\Lattice\Http\Page as BasePage;

#[AsPage(route: '/dashboard', middleware: ['web'])]
final class DashboardPage extends BasePage
{
    public function title(): string
    {
        return 'Dashboard';
    }

    public function render(PageSchema $schema): PageSchema
    {
        return $schema->schema([
            Stack::make('dashboard')
                ->gap(Gap::Large)
                ->schema([
                    Heading::make('Dashboard'),
                    Text::make('Everything below is described in PHP and rendered as React.'),
                    Grid::make('stats')
                        ->columns(2)
                        ->schema([
                            Card::make('Orders', '128 this week.'),
                            Card::make('Revenue', '$4,210 this week.'),
                        ]),
                ]),
        ]);
    }
}
Dynamic route parameters map natively directly into the render() method argument signatures using Laravel’s implicit Route-Model Binding rules. Additionally, a dedicated authorize() gateway allows you to evaluate user permissions before any rendering actions execute:

TypeScript
#[AsPage(route: '/products/{product}/edit')]
class ProductEditPage extends Page
{
    public function authorize(Request $request): bool
    {
        return $request->user()?->can('update', Product::class) ?? false;
    }

    public function render(PageSchema $schema, Product $product): PageSchema
    {
        return $schema->schema([
            Heading::make("Edit {$product->name}"),
        ]);
    }
}
Form Definitions
Forms are managed by extending the base FormDefinition class, which handles field layout declarations and processing logic. Lattice renders the matching interactive client-side React inputs, validates incoming HTTP requests using standard Laravel validation structures, and processes the payload using the handle() method upon validation success:

TypeScript
use Illuminate\Http\Request;
use Lattice\Lattice\Attributes\AsForm;
use Lattice\Lattice\Forms\Components\Form as FormComponent;
use Lattice\Lattice\Forms\Components\TextInput;
use Lattice\Lattice\Forms\FormDefinition;
use Symfony\Component\HttpFoundation\Response;

#[AsForm('app.profile.form')]
class ProfileForm extends FormDefinition
{
    public function definition(FormComponent $form, Request $request): FormComponent
    {
        return $form->schema([
            TextInput::make('name', 'Name')->rules(['required', 'string', 'max:255']),
            TextInput::make('email', 'Email')->email()->rules(['required', 'email']),
        ]);
    }

    public function handle(Request $request): Response
    {
        $validated = $this->validate($request);
        $request->user()->update($validated);

        return redirect('/profile');
    }
}
Integrating a designed form block onto any layout requires just a single fluid method call. Here you can configure the HTTP verb, update the submission action label, and pre-populate fields. Appending ->precognitive(500) seamlessly adds real-time background validation rules:

TypeScript
Form::use(ProfileForm::class)
    ->method(HttpMethod::Patch)
    ->submitLabel('Save changes')
    ->precognitive(500)
    ->fill([
        'name' => $user->name,
        'email' => $user->email,
    ]);
Creating Data Tables
Data grids extend the EloquentTableDefinition class. You simply register the column schema and provide an Eloquent query builder instance; filtering, pagination, and sorting layers are automatically handled on your behalf based on fluent modifiers like sortable() or filterable():

TypeScript
use Illuminate\Database\Eloquent\Builder;
use Lattice\Lattice\Attributes\AsTable;
use Lattice\Lattice\Tables\Columns\BooleanColumn;
use Lattice\Lattice\Tables\Columns\NumberColumn;
use Lattice\Lattice\Tables\Columns\TextColumn;
use Lattice\Lattice\Tables\EloquentTableDefinition;
use Lattice\Lattice\Tables\TableQuery;

#[AsTable('app.products')]
class ProductsTable extends EloquentTableDefinition
{
    public function columns(): array
    {
        return [
            TextColumn::make('name')->sortable()->filterable(),
            NumberColumn::make('price')->sortable()->filterable(),
            BooleanColumn::make('featured'),
            TextColumn::make('updated_at')->date('Y-m-d')->sortable(),
        ];
    }

    public function builder(TableQuery $query): Builder
    {
        return Product::query();
    }
}
Rendering this custom table onto a core view reuses the explicit static ::use() pattern seen in forms, allowing clean and modular components:

TypeScript
$schema->schema([
    Heading::make('Products'),
    Table::use(ProductsTable::class),
]);
Actions and Responsive Client Effects
Actions capture the true power of Lattice's client-server synchronization loop. An action extends ActionDefinition, handles visual button rendering inside definition(), and evaluates processing scripts on the server via handle(). Instead of returning a traditional redirect or HTML view, it returns an explicit ActionResult object containing client effects—real-time directives for the client to process, such as displaying alert toasts or refreshing specific UI components:

TypeScript
use Illuminate\Http\Request;
use Lattice\Lattice\Actions\ActionDefinition;
use Lattice\Lattice\Actions\ActionResult;
use Lattice\Lattice\Actions\Components\Action;
use Lattice\Lattice\Attributes\AsAction;
use Lattice\Lattice\Core\Enums\ButtonVariant;
use Lattice\Lattice\Core\Enums\Variant;

#[AsAction('app.products.archive')]
class ArchiveProductAction extends ActionDefinition
{
    public function definition(Action $action): Action
    {
        return $action
            ->label('Archive')
            ->variant(ButtonVariant::Destructive)
            ->confirm('Archive product?', 'This hides it from the catalogue.');
    }

    public function handle(Request $request): ActionResult
    {
        $product = $this->product($request);
        $product->update(['status' => 'archived']);

        return ActionResult::success()
            ->toast(Variant::Success, 'Product archived.')
            ->reloadComponent('app.products');
    }
}
When registering actions inside individual data grid rows, the system automatically binds the record context data to the backend, ensuring the handle() method knows exactly which database record it is modifying:

TypeScript
Action::use(ArchiveProductAction::class)
    ->context(['product_id' => $row['id']]);
```
