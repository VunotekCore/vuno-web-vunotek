---
name: Vunotek Architectural System
colors:
  surface: '#0b1326'
  surface-dim: '#0b1326'
  surface-bright: '#31394d'
  surface-container-lowest: '#060e20'
  surface-container-low: '#131b2e'
  surface-container: '#171f33'
  surface-container-high: '#222a3d'
  surface-container-highest: '#2d3449'
  on-surface: '#dae2fd'
  on-surface-variant: '#bfc9c3'
  inverse-surface: '#dae2fd'
  inverse-on-surface: '#283044'
  outline: '#89938d'
  outline-variant: '#404944'
  surface-tint: '#95d3ba'
  primary: '#95d3ba'
  on-primary: '#003829'
  primary-container: '#064e3b'
  on-primary-container: '#80bea6'
  inverse-primary: '#2b6954'
  secondary: '#69dca4'
  on-secondary: '#003823'
  secondary-container: '#28a471'
  on-secondary-container: '#00311e'
  tertiary: '#77dd6d'
  on-tertiary: '#003a05'
  tertiary-container: '#00500a'
  on-tertiary-container: '#62c75a'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#b0f0d6'
  primary-fixed-dim: '#95d3ba'
  on-primary-fixed: '#002117'
  on-primary-fixed-variant: '#0b513d'
  secondary-fixed: '#86f9be'
  secondary-fixed-dim: '#69dca4'
  on-secondary-fixed: '#002112'
  on-secondary-fixed-variant: '#005234'
  tertiary-fixed: '#92fa86'
  tertiary-fixed-dim: '#77dd6d'
  on-tertiary-fixed: '#002202'
  on-tertiary-fixed-variant: '#00530b'
  background: '#0b1326'
  on-background: '#dae2fd'
  surface-variant: '#2d3449'
  vue-green: '#42b883'
  node-green: '#339933'
  forest-deep: '#022c22'
  surface-charcoal: '#1e293b'
  slate-text: '#94a3b8'
typography:
  display-lg:
    fontFamily: Hanken Grotesk
    fontSize: 64px
    fontWeight: '700'
    lineHeight: 72px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Hanken Grotesk
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Hanken Grotesk
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-md:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-mono:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 24px
  lg: 48px
  xl: 80px
  gutter: 24px
  margin: 32px
---

## Brand & Style

The design system is engineered to reflect the precision of high-performance software development. The brand identity bridges the gap between the "Vu" (Vue.js) and "tek" (technology), positioning the agency as a specialized authority in modern full-stack engineering.

The chosen design style is **Corporate / Modern** with a **Minimalist** foundation. It prioritizes clarity, structural integrity, and performance. The aesthetic is "Engineering-First"—utilizing clean lines, ample whitespace to reduce cognitive load, and a sophisticated dark-biased palette that resonates with the developer community while maintaining executive-level professionalism. The UI should feel like a high-end IDE: functional, reliable, and powerful.

## Colors

The palette is anchored by a sophisticated "Forest Tech Green" primary color, providing a more mature and stable foundation than typical startup greens. 

- **Primary:** A deep, emerald-forest green used for key structural elements and high-level branding.
- **Secondary (Vue-Green):** Used for interactive elements, primary call-to-actions, and success states, honoring the Vue.js lineage.
- **Tertiary (Node-Green):** Reserved for accents, code-related blocks, and subtle iconography to provide depth to the green spectrum.
- **Backgrounds:** The system defaults to a "Deep Slate" dark mode (#0f172a) to evoke a professional terminal environment. Surfaces use hierarchical layering (charcoal to slate) to create depth without relying on pure black.

## Typography

Typography is used to reinforce the "software engineering" vibe. 

- **Headlines:** Hanken Grotesk provides a sharp, contemporary look with high legibility, perfect for conveying cutting-edge innovation.
- **Body:** Inter is the workhorse for all prose, ensuring clarity and a neutral, professional tone.
- **Data/Labels:** JetBrains Mono is introduced for small labels, technical specifications, and code snippets, grounding the design in the world of development.

Scale is strictly managed to ensure hierarchy. Display type should use tighter letter spacing to maintain a "locked-in" architectural feel.

## Layout & Spacing

This design system employs a **Fixed Grid** philosophy for desktop (12 columns, 1200px max-width) and a **Fluid Grid** for mobile. 

The rhythm is dictated by a strict **8px linear grid**. All dimensions, padding, and margins must be multiples of 8. 
- **Desktop:** 24px gutters, 80px vertical section spacing to create an "airy" and premium feel.
- **Mobile:** 16px margins, 48px vertical section spacing.
- **Alignment:** Content blocks should align strictly to the grid edges to evoke the structured nature of code.

## Elevation & Depth

To maintain a modern "tech-focused" aesthetic, depth is communicated through **Tonal Layers** rather than heavy shadows.

1.  **Level 0 (Base):** Deep Slate (#0f172a).
2.  **Level 1 (Cards/Containers):** Charcoal (#1e293b) with a 1px subtle border (#334155).
3.  **Level 2 (Dropdowns/Modals):** Lighter Slate with a "Soft Ambient Shadow"—low opacity (15%), 20px blur, tinted with the primary forest green to keep the dark mode from feeling "flat" or "gray."

Avoid skeuomorphism. Depth should feel like stacked sheets of glass or metal, consistent with the "tek" aspect of the brand.

## Shapes

The shape language is **Soft** but disciplined. 

A 4px (`0.25rem`) base radius is used for small components like checkboxes and tags. Standard components like cards and buttons use an 8px (`0.5rem`) radius. This subtle rounding prevents the UI from feeling "sharp" or "hostile" (Brutalist) while maintaining a more professional, engineering-centric structure than "bubbly" consumer apps.

## Components

- **Buttons:** 
  - *Primary:* Vue-Green background with bold Forest-Deep text. High contrast is mandatory.
  - *Secondary:* Ghost style with 1px Vue-Green border and mono-label typography.
- **Cards:** No shadows. Use "Surface-Charcoal" backgrounds and a 1px border. On hover, the border should transition to Vue-Green to indicate interactivity.
- **Input Fields:** Darker than the surface background, using JetBrains Mono for placeholder text to emphasize the "developer" focus. Focus states should use a 2px solid Node-Green glow.
- **Chips/Badges:** Use the monospaced label font. Success badges use Node-Green; informational badges use the primary Forest-Deep with a light green border.
- **Tech Icons:** Use "Duotone" icons with the secondary color for the primary shape and the tertiary color for the accent shape.
- **Code Blocks:** Syntax highlighting should be customized to the Vunotek palette, emphasizing the green spectrum for functions and variables.