# Design System Strategy: The Elevated Opinion

## 1. Overview & Creative North Star
This design system is built to transform a standard utility—surveying—into a premium, editorial experience. Moving away from the "generic SaaS" look, we adopt a **Creative North Star** of **"The Digital Curator."**

The experience is defined by intentional breathing room, authoritative typography, and a "soft-layered" architecture. Instead of rigid grids and harsh dividers, the layout utilizes organic asymmetry and tonal depth to guide the user. Every interaction should feel like flipping through a high-end design journal: tactile, purposeful, and clean.

## 2. Colors: Tonal Architecture
The palette is anchored by a vibrant, sophisticated blue, supported by a rich spectrum of "off-white" and "cool-grey" surfaces.

### The Foundation
*   **Primary (`#0058be`):** Our signature color. Use this for moments of high intent.
*   **Surface (`#f9f9ff`):** The default canvas. A slightly cooled white to reduce eye strain and feel more "custom" than pure hex #FFFFFF.
*   **Surface Container Tiers:** 
    *   `lowest`: #ffffff (Used for the highest level of focus, like a card on a tinted section).
    *   `low`: #f2f3fd
    *   `high`: #e6e7f2
    *   `highest`: #e1e2ec

### The "No-Line" Rule
**Explicit Instruction:** Prohibit the use of 1px solid borders for sectioning or containment. Boundaries must be defined solely through background color shifts. For example, a `surface-container-low` header should sit against a `surface` body. 

### The "Glass & Gradient" Rule
To add visual "soul," use subtle gradients in the `primary` to `primary-container` range for Hero backgrounds (as seen in the large dashboard banners). For floating elements or navigation headers, use **Glassmorphism**: semi-transparent surface colors with a `backdrop-blur` of 12px to 20px.

---

## 3. Typography: Editorial Authority
We utilize a two-family system to create a high-contrast, editorial feel.

*   **Display & Headlines (Manrope):** A modern sans-serif with a geometric touch. Used for large titles to establish an "authoritative" voice.
    *   *Scale Example:* `display-lg` (3.5rem) for hero statements; `headline-md` (1.75rem) for section titles.
*   **Body & Labels (Inter):** A high-legibility typeface designed for digital screens. 
    *   *Scale Example:* `body-lg` (1rem) for survey descriptions; `label-md` (0.75rem) for metadata and tags.

**Typographic Intent:** Use massive scale differences. A `display-lg` headline should dwarf its accompanying `body-md` description to create a clear visual hierarchy that feels curated rather than templated.

---

## 4. Elevation & Depth: Tonal Layering
Depth in this system is not about "dropping shadows" on everything; it’s about physical stacking and ambient light.

*   **The Layering Principle:** Place a `surface-container-lowest` card on a `surface-container-low` section to create a soft, natural lift. This creates "visual compartments" without the clutter of lines.
*   **Ambient Shadows:** Use shadows sparingly. When a floating effect is required (e.g., a "Create Survey" FAB), use a shadow with a large blur (20px+) and low opacity (max 8%). The shadow color should be a tinted version of the `on-surface` color, never pure black.
*   **The "Ghost Border" Fallback:** If a border is required for accessibility, it must be a "Ghost Border": use the `outline-variant` token at **10% - 20% opacity**.

---

## 5. Components: Refined Interaction

### Cards & Lists
*   **Rule:** Forbid divider lines. Separate content using vertical white space (using the `xl` spacing token) or subtle shifts in surface color.
*   **Rounding:** All cards must use `xl` (1.5rem) or `lg` (1rem) corner radii to maintain a soft, approachable aesthetic.

### Buttons
*   **Primary:** A gradient-filled container (`primary` to `primary_container`) with `full` rounding (pill-shape).
*   **Secondary:** Ghost-style. No background, only a "Ghost Border" (20% opacity `outline`) or a light `primary-fixed-dim` background.

### Input Fields
*   **Style:** Minimalist. Use `surface-container-highest` for the background of the input area with no border. On focus, transition the background to `surface-container-low` and add a 2px `primary` bottom-border only.

### Chips & Tags
*   **Usage:** Use for categories (e.g., "Politics", "Education"). Style with `secondary-container` backgrounds and `on-secondary-container` text. Keep corners `full`.

---

## 6. Do’s and Don’ts

### Do
*   **DO** use whitespace as a functional element. If a layout feels "crowded," double the padding.
*   **DO** align text-heavy sections to a generous left margin to create an editorial "column" feel.
*   **DO** use semi-transparent overlays for modals to keep the background context visible (Glassmorphism).

### Don't
*   **DON’T** use #000000 for text. Use `on-surface` (#191b23) to keep the contrast high but the feel "soft."
*   **DON’T** use "Default" card shadows from CSS frameworks. Always custom-tune the blur and opacity to be ambient and light.
*   **DON’T** use standard 1px borders to separate survey questions. Use a `surface-container` background shift for each question block.