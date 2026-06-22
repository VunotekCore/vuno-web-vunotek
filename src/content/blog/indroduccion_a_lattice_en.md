---
title: 'Introducción a Lattice: Diseñando UIs de Inertia directamente en PHP'
excerpt: 'Descubre Lattice, el nuevo framework guiado por el servidor para Laravel que permite construir pantallas complejas con React e Inertia sin salir de PHP.'
date: 2026-06-17
category: Laravel
author: Paul Redmond
locale: es
image: blog/lattice-php-inertia
---

Lattice es un framework de interfaz de usuario guiado por el servidor (_server-driven_) diseñado específicamente para el ecosistema Laravel. Su propósito principal es permitirte describir por completo una interfaz —incluyendo sus vistas, formularios, tablas, menús y acciones— utilizando código PHP estructurado, delegando el renderizado automático a componentes reales de React mediante Inertia.js. Bajo este enfoque, el servidor se convierte en la única fuente de verdad sobre la estructura de la aplicación, mientras que el cliente se limita exclusivamente a interpretar y pintar dicha interfaz.

En la práctica, cada vista es una clase de PHP encargada de orquestar un árbol de definiciones de componentes. Lattice se ocupa de serializar este árbol en un payload fuertemente tipado que viaja a través de Inertia de la misma forma que una visita estándar. Posteriormente, un componente React maestro en el frontend resuelve cada nodo del árbol consultando un registro de componentes preestablecido.

## Ventajas Clave del Modelo de Lattice

- **Enrutamiento automático basado en clases:** Al añadir el atributo `#[AsPage]`, la ruta se registra de forma dinámica. Lattice inspecciona tus directorios configurados y conecta los endpoints sin necesidad de mapearlos manualmente en tus archivos de rutas.
- **Formularios con validación nativa de Laravel:** Los campos se declaran en PHP y se validan mediante las reglas estándar del framework, ofreciendo además soporte nativo para validación en tiempo real con Laravel Precognition.
- **Tablas dinámicas impulsadas por Eloquent:** El ordenamiento, paginado y filtrado de datos se resuelven de forma transparente configurando clases de tablas asociadas a un constructor de consultas de Eloquent.
- **Acciones en el servidor con efectos en el cliente:** Al hacer clic en un elemento, se ejecuta código PHP en el backend que retorna instrucciones explícitas (_effects_) para el frontend, tales como mostrar notificaciones tipo _toast_, recargar componentes o redirigir al usuario.

## Estructuración de Páginas

Cualquier página en Lattice hereda de la clase base `Page`, incorpora el atributo `#[AsPage]` para definir su ruta y expone el método `render()`. En lugar de escribir código JSX o Blade, la interfaz se ensambla utilizando una interfaz fluida en PHP con componentes de diseño como `Stack`, `Grid`, `Heading` o `Card`:

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

La inyección de parámetros dinámicos de las rutas se resuelve de forma automática en la firma del método render() gracias al Route-Model Binding de Laravel. Asimismo, puedes implementar el método authorize() para restringir o permitir el acceso a la pantalla antes de procesar su estructura:

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

Gestión de Formularios
Los formularios se modelan extendiendo la clase FormDefinition. En ellos se definen tanto los inputs del formulario como la lógica de procesamiento. Lattice renderizará los controles de React correspondientes, validará los datos en base a las reglas configuradas y ejecutará el método handle() tras superar con éxito la validación:

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

Para integrar un formulario en una página basta con invocarlo mediante su interfaz fluida, la cual permite definir el método HTTP, el texto del botón de envío y el estado inicial de los datos. Si añades la directiva ->precognitive(500), habilitarás la validación en tiempo real asíncrona:

Form::use(ProfileForm::class)
    ->method(HttpMethod::Patch)
    ->submitLabel('Save changes')
    ->precognitive(500)
    ->fill([
        'name' => $user->name,
        'email' => $user->email,
    ]);

Creación de Tablas de Datos
Para presentar datos tabulares de manera fluida, extendemos EloquentTableDefinition. Únicamente debes especificar las columnas necesarias y retornar la consulta base; las operaciones de ordenamiento, filtros y paginación se auto-configuran al encadenar métodos como sortable() o filterable():

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

Para pintarla dentro del método render() de una página se aprovecha el mismo patrón estático ::use(), permitiendo una composición limpia y declarativa:

$schema->schema([
    Heading::make('Products'),
    Table::use(ProductsTable::class),
]);

Acciones y Efectos Reactivos en el Cliente
Las acciones cierran el ciclo de comunicación bidireccional de Lattice. Al extender ActionDefinition, defines los elementos visuales del botón en definition() y delegas el procesamiento lógico en handle(). La diferencia radical es que en lugar de retornar una redirección tradicional o una vista completa, devuelves un ActionResult que contiene instrucciones secuenciales para que el cliente ejecute de inmediato (como alertas instantáneas o actualizaciones parciales de componentes):


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

Cuando vinculas estas acciones directamente a las filas de una tabla, Lattice se encarga de transferir de forma automática el contexto del registro seleccionado para que el backend reconozca la entidad exacta sobre la cual operar:

Action::use(ArchiveProductAction::class)
    ->context(['product_id' => $row['id']]);


```
