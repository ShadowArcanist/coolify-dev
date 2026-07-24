# Coolify UI Redesign — Graphite shell + Coollabs layer-card settings surfaces

A visual redesign of Coolify on the existing **Livewire + Blade + Alpine + Tailwind v4**
stack. No React, no framework change — Blade views are re-skinned and design
tokens live in CSS.

The redesign happened in two phases and both are live on this branch:

1. **Graphite shell** — sidebar, topbar, dashboard (dark, layered-neutral UI).
2. **Coollabs layer-card settings surfaces** — the application
   *Configuration → General* page was rebuilt with the Coollabs layer design
   language (layered neutral surfaces, compact card headers, custom dropdowns,
   empty states). This phase defines the pattern for restyling **all remaining
   settings pages** (Advanced, Environment Variables, Healthcheck, Resource
   Limits, servers, databases, services, …). Follow this document when doing so.

> **Ground rules from the maintainer (read first):**
> - **Frontend only.** Features shown in the UI do NOT need backend support.
>   It is fine to render controls that aren't persisted (see `:wire="false"`
>   listboxes and the extra basic-auth users below).
> - **Do not write or run tests.** Validate Blade syntax with
>   `docker exec coolify php artisan view:cache` (then `view:clear`) instead.
> - Iterate in small steps; the maintainer reviews screenshots after each change.
> - Prefer editing existing components; keep superseded ones in place so they
>   can be deprecated slowly later (don't delete legacy controls).

---

## 1. Development workflow

- Dev environment runs in Docker. PHP lives in the `coolify` container:
  `docker exec coolify php artisan …`. There is **no php on the host**.
- Vite dev server runs in the `coolify-vite` container with HMR. Verify CSS
  compiled: `curl -s http://localhost:5173/resources/css/app.css | grep <needle>`
  and `docker logs coolify-vite --tail 5`.
- `npm run build` on the host Mac is **broken** (missing
  `@rolldown/binding-darwin-arm64`; fix is `rm -rf node_modules package-lock.json && npm i`).
  Not needed for dev — the vite container serves assets.
- Blade syntax check after edits:
  `docker exec coolify php artisan view:cache && docker exec coolify php artisan view:clear`.

---

## 2. ⚠️ CSS cascade gotchas (the #1 source of "my styles don't apply")

`resources/css/app.css` contains **unlayered global element rules** (`h1`–`h4`,
`section { margin-bottom: 3rem }`, `label`, `a`, table rules, …). Tailwind v4
puts utilities in `@layer utilities`. **Unlayered CSS always beats layered CSS**,
so:

- Utility classes on headings/sections/labels in markup (e.g. `text-sm` on an
  `<h3>`) are silently overridden by those globals.
- Rules added via `@layer components` **lose to utilities** (layer order:
  theme → base → components → utilities), so component overrides written there
  for `.input`/`.button` never applied.

**The fix used everywhere in this redesign:** all settings-surface styling is
**plain unlayered CSS at the END of `resources/css/app.css`** (section starts at
the comment `Coollabs layer-card settings surfaces`). Class selectors there
beat both the element globals and the utility layers. When you restyle a new
page, put its CSS in that block — do not fight the cascade with utilities on
`h3`/`h4`/`label`/`section` elements.

Other gotchas learned the hard way:

- **Monaco/Alpine `x-init` attributes are double-quoted HTML attributes.** Any
  JS you inject there must not contain literal `"` — use `"` in regexes
  and single quotes for strings (see the nginx Monarch grammar in
  `monaco-editor.blade.php`).
- Alpine `$refs` are scoped: a parent element's handler cannot see a child
  `x-data`'s refs.
- Blade `@entangle('prop')` works inside `x-data` attribute strings; append
  `.live` for live sync (equivalent to `wire:model.live`).
- `x-slot` inside `@if`/`@can` works fine for conditional slots.

---

## 3. Design tokens

### 3.1 Coollabs surface tokens (settings surfaces)

Defined as CSS variables in the unlayered block of `app.css` (`:root` = light,
`.dark` = dark). A pure-neutral oklch scale (zero chroma):

| Var | Light | Dark | Role |
|---|---|---|---|
| `--coollabs-canvas` | `oklch(98.75% 0 0)` | `oklch(10% 0 0)` | page background (neutral near-black, **not pure black**) |
| `--coollabs-elevated` | `oklch(98% 0 0)` | `oklch(15% 0 0)` | card shell + card header strip |
| `--coollabs-recessed` | `oklch(96% 0 0)` | `oklch(20% 0 0)` | input/select/listbox fills |
| `--coollabs-base` | `#fff` | `oklch(17% 0 0)` | card body (the nested inner panel) |
| `--coollabs-fill` | `oklch(92.2% 0 0)` | `oklch(26.9% 0 0)` | body ring, chips, selected pills, in-card dividers |
| `--coollabs-line` | `oklch(14.5% 0 0 / .1)` | `oklch(32% 0 0)` | input/listbox/pill borders |
| `--coollabs-hairline` | `oklch(93.5% 0 0)` | `oklch(26.9% 0 0)` | card shell ring |
| `--coollabs-subtle` | `oklch(55.6% 0 0)` | `oklch(70.8% 0 0)` | card titles, field labels, muted text |

**Dark surface ladder (memorize):** page `10%` < shell/header `15%` < body `17%`
< inputs `20%` < rings/fills `26.9%` < borders `32%`.

The app-wide `--color-panel` token (in `@theme`) is `oklch(10% 0 0)` — sidebar,
topbar, content area (`<main>` uses `bg-white dark:bg-panel` in
`layouts/app.blade.php`) and the unsaved-bar all share the exact canvas color.
Light-mode content is `bg-white` to match the white sidebar.

### 3.2 Graphite tokens (shell: sidebar/topbar/dashboard)

Still in `@theme`: `--color-app #0c0c0d`, `--color-surface #161618`,
`--color-raised #1c1c1e`, `--color-fg #f2f2f2`, `--color-fg-dim #b4b4b8`,
`--color-fg-faint #6e6e74`, `--color-accent #4c8dff`, `--color-hairline
rgba(255,255,255,.08)`. Hairline borders = `border-white/[0.06-0.12]`.

---

## 4. The layer card (every settings card)

Anatomy:

- **Root shell**: `bg: --coollabs-elevated`, `border-radius: 8px`, 1px ring
  (`box-shadow: 0 0 0 1px var(--coollabs-hairline)`), `scroll-margin-top: 4.5rem`
  (so `scrollIntoView` clears the fixed 48px topbar).
- **Header**: compact strip on the shell — `padding: .5rem 1rem`, 14px/500
  title in `--coollabs-subtle`, optional gray ⓘ tooltip and a right-aligned
  actions slot. **No border under the header** — the layering does the
  separation. Cards keep `overflow: visible` so floating dropdown panels can
  escape.
- **Body**: its own nested rounded-lg panel — `bg: --coollabs-base`, 1px
  `--coollabs-fill` ring, `padding: 1rem`.

Blade component: `resources/views/components/application/settings-section.blade.php`

```blade
<x-application.settings-section
    id="optional-anchor"
    title="Public access"
    helper="Tooltip text for the gray ⓘ next to the title">
    <x-slot:actions> …right-aligned header buttons… </x-slot:actions>
    …body…
</x-application.settings-section>
```

Page layout: cards stack **full-width, single column**, `flex flex-col gap-4`
(no side-by-side cards). The page wrapper is
`<form class="application-settings-form …">` — the scoped CSS keys off
`.application-settings-form` / `.application-settings-workspace`, so keep one
of those classes on any new page you restyle.

---

## 5. Controls (all in the unlayered CSS block)

Heights are uniform **32px** (`2rem`) for inputs, selects, buttons, listboxes,
pills; radius **8px** everywhere (10px for Monaco editors).

| Thing | Where | Notes |
|---|---|---|
| Inputs/selects | `.application-settings-form .input/.select` | recessed fill, `--coollabs-line` border, accent focus ring, 13px/500 labels in `--coollabs-subtle` |
| Buttons | `.application-settings-form .button` | 32px, radius 8, matches `x-forms.button` |
| **Listbox (custom dropdown)** | `x-forms.listbox` + `.listbox-trigger/-panel/-option` | see §5.1 — use this instead of native `<select>` everywhere |
| **Chip input** | `.chip-input`, `.chip`, `.chip-remove` | multi-value entry (Domains/URLs). Chips = `--coollabs-fill`, no border. Enter & comma & paste-with-commas add; Backspace removes last; placeholder only when empty. 3px block padding keeps total height exactly 32px |
| Option pills | `.option-pill-group`, `.option-pill` | segmented radio groups rendered as detached pills (`peer` + sibling). Currently unused (all converted to listboxes) but kept for future use |
| **Empty state** | `x-empty` (`components/empty.blade.php`) | `size sm/base/lg`, `icon`/`contents` slots. Use when a section is non-functional in the current state |
| Footer save bar | `components/unsaved-bar.blade.php` | fixed full-width bottom bar, appears via `wire:dirty`, "← Cancel" + blue `#0e6ef4` "Save changes" |
| Icons | `x-reicon name="…"` | filled 24×24 glyphs, `currentColor`. Added this phase: `eye`, `eye-off`, `globe` |

### 5.1 `x-forms.listbox` — the custom dropdown

`resources/views/components/forms/listbox.blade.php`. Alpine-based; Livewire
binding via `@entangle`. Props:

- `id` — Livewire property to bind (entangled).
- `options` — list of `['value' => …, 'label' => …, 'disabled' => bool]`
  (values may be bools; comparison is `String(a) === String(b)`).
- `live` — entangle `.live` (use when changing the value must re-render the
  page, e.g. `buildPack`).
- `onChange` — `$wire` method called after selection (e.g. `instantSave`,
  `setSiteType`) — this is how instant-persist dropdowns work.
- `wire="false"` + `value="…"` — **purely client-side** control for
  frontend-only features (e.g. "Protocol redirection").
- Forwarded: `x-bind:disabled` lands on the trigger button.
- `label`, `helper`, `required`, `placeholder`.

Panels float (`position:absolute; z-30`) — this is why cards must keep
`overflow: visible`.

### 5.2 Monaco editors

`components/forms/monaco-editor.blade.php` (used via
`x-forms.textarea … useMonacoEditor monacoEditorLanguage="…"`):

- Custom **`coolify-dark`** theme: `editor.background #0b0b0c`, subtle
  translucent scrollbar sliders; light mode stays `vs`.
- Container: 10px radius, `--coollabs-line` border, `overflow: hidden`
  (`.coolify-monaco-editor` in app.css).
- Options: `renderLineHighlight: 'none'`, `padding: {top:12,bottom:12}`, 8px
  scrollbars, overview ruler fully disabled.
- **Height follows `rows`**: `textarea.blade.php` sets
  `--editor-height: rows*23 + 38px`; without `rows` it falls back to
  near-viewport height (min 150px).
- Custom **nginx** Monarch language is registered when
  `monacoEditorLanguage="nginx"` (comments, `$vars`, ~50 directives, strings,
  units, operators). Remember the `"` quoting rule (§2) when editing it.

---

## 6. UX conventions (apply to every page you restyle)

1. **Sentence case everywhere** — "Base directory", "Ports exposes", "Custom
   Docker options". Proper nouns keep caps (Docker, Nginx, Dockerfile, SPA, URLs).
2. **Dropdowns over checkboxes**, with self-explanatory option labels:
   - booleans become two options, e.g. Label management → "Managed by Coolify
     (auto-generated)" / "Managed manually (edit labels yourself)";
     Builder selection → "Deployment server" / "Available build server
     (auto-select)"; Authentication → "None" / "HTTP Basic Authentication".
   - paired booleans can merge into one dropdown (isStatic+isSpa → "Site type":
     Dynamic / Static / SPA (single-page application), via the UI-only
     `siteType` property + `setSiteType()` in `General.php`).
3. **Redirect phrasing**: "Redirect X to Y" / "Allow X & Y".
4. **Label-row actions**: a big action related to one field goes right-aligned
   in that field's label row (nginx "Generate default", labels "Reset to
   defaults") — not as a full-width button.
5. **Header actions slot** for card-level actions.
6. **Equal-width field grids**: `grid gap-4 sm:grid-cols-2`. For rows that end
   in a button use `sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]`; to make a
   lone control align with such rows, add an `invisible` copy of the button as
   the third cell (see Security's Authentication row).
7. **Repeaters**: "Add user"-style lists use a `.button` "Remove user" per row
   (disabled + tooltip on the non-removable default row) and a `.button`
   "＋ Add …" below. Extra rows may be Alpine-local only (frontend-only rule).
8. **Empty states instead of dead controls**: when a card is ineffective in the
   current configuration (e.g. Public access & Security while container labels
   are "Managed manually" — basic auth and domains are applied via proxy
   labels), render `<x-empty size="sm">` with an explanation and a button that
   `scrollIntoView`s the controlling card (give that card an `id`).
9. Long helper text goes in the gray/yellow ⓘ tooltip (`x-helper`), not inline.
10. Password fields: filled reicon `eye`/`eye-off` toggle (shared input
    component already does this; replicate for raw inputs).

---

## 7. Current state of the General page (reference implementation)

`resources/views/livewire/project/application/general.blade.php` — card order:

1. **Application details** — Name, Description.
2. **Public access** — URLs chip-input + "Generate domain"; "Domain
   redirection" + "Protocol redirection" (frontend-only) dropdowns. Empty state
   when labels are user-managed. Compose apps: per-service domain inputs.
3. **Build pipeline** — Build strategy + Web server (static image) dropdowns →
   Site type → directories/watch paths → install/build/start commands →
   Builder selection → "Custom Nginx configuration" editor (label-row action).
   Docker-image apps show a "Nothing to build" note.
4. **Container image** — Image + Tag (equal columns, real placeholders like
   `nginx` / `alpine`).
5. **Networking** — port callouts, Ports exposes / Port mappings / Network
   aliases.
6. **Runtime** — Custom Docker options.
7. **Security** — Authentication dropdown → credential rows with Remove
   user/Add user (extra rows frontend-only). Empty state when labels
   user-managed.
8. **Deployment lifecycle** — Pre/Post-deployment side by side.
9. **Container labels** (`id="container-labels-section"`) — Label management +
   Special characters dropdowns → divider → "Active labels" editor with "Reset
   to defaults" in its label row.

Backend touches made (kept minimal): `General.php` gained `siteType` +
`setSiteType()` only. Everything else is view/CSS.

Hidden for now: the "Compose parser N" dev hint and the "View details"
resource-details modal trigger (left as a Blade comment near the top of the
view).

---

## 8. Where things live

| File | Contents |
|---|---|
| `resources/css/app.css` | `@theme` tokens; **unlayered layer-card block at the end** (cards, inputs, listbox, chips, pills, purpose-rows, monaco container) |
| `resources/css/utilities.css` | `@utility` classes for the shell (`button`, `menu-item*`, `box`, …) |
| `resources/views/components/application/settings-section.blade.php` | layer card |
| `resources/views/components/forms/listbox.blade.php` | custom dropdown |
| `resources/views/components/empty.blade.php` | empty state |
| `resources/views/components/unsaved-bar.blade.php` | footer save bar |
| `resources/views/components/reicon.blade.php` | icon map (add glyphs here, `currentColor`) |
| `resources/views/components/forms/monaco-editor.blade.php` | theme, nginx language, editor options |
| `resources/views/components/forms/textarea.blade.php` | rows → `--editor-height` bridge |
| `resources/views/components/forms/input.blade.php` | reicon password toggle |
| `resources/views/layouts/app.blade.php` | shell; `<main>` = `bg-white dark:bg-panel` |
| `resources/views/components/navbar.blade.php` | graphite sidebar (phase 1) |

## 9. Playbook for restyling the next page

1. Wrap the page in a form/div carrying `application-settings-form` (or add the
   class to the scoped CSS selectors).
2. Convert each visual group into an `<x-application.settings-section>` with a
   sentence-case title + ⓘ helper; stack them `flex flex-col gap-4`.
3. Replace native selects and checkboxes with `x-forms.listbox`
   (instant-persist via `onChange="instantSave"` where the old control did).
4. Apply the grid/label/action conventions from §6; keep Livewire ids/bindings
   untouched so persistence keeps working.
5. Big editors → Monaco with `rows`, comma-lists → `.chip-input`, dead states →
   `x-empty`.
6. Validate: `view:cache`/`view:clear` in the coolify container, hard-refresh,
   screenshot. **No tests.**
