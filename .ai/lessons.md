# Lessons

## Don't add speculative toggles for edge cases
When the feature conceptually requires N inputs (e.g. load balancing requires 2+ servers), don't add a toggle that pretends it could work with 1. Treat the degenerate state as an error/callout, not an opt-in. `deploy_on_lb_server` was such a toggle — the user rejected it because with 1 server, LB and backend collapse to the same node so there's nothing to balance.

## When mirroring a UI pattern, mirror the visual structure too
"Like the gateway routes page" meant the expandable-card-per-item layout, not just the data-flow pattern (YAML → SSH → dynamic/). First pass used plain rows; user called it out. Lesson: when told to follow a pattern, open that view, copy the card shell (`x-data expanded`, chevron, header summary, `x-collapse` body with `readonlyClass` inputs), and reuse it verbatim.

## Keep form sections by concern, not by visual convenience
Sticky sessions belong in "Session affinity", not squeezed into the same row as the algorithm select because there happened to be empty space. Each form section should be one concern with a clear `<h3>`.

## Feature naming — favor user-facing clarity
"High Availability" is broader than load balancing (implies failover, replication, etc.). The user wanted "Load Balancing" — the literal name of what the page does. Prefer precise names over aspirational ones.

## Don't be conservative — verify capabilities via docs
First pass shipped only "Weighted Round Robin" because I wasn't sure what Traefik v3 CE File provider supported. The user asked "why only one algorithm?". Lesson: when a feature dimension has multiple real options upstream, verify via official docs (WebSearch / docs site) before narrowing. Traefik v3 CE supports `wrr`, `p2c`, `hrw`, `leasttime` on `loadBalancer.strategy` — all should be exposed.

## Enable/Disable buttons > checkboxes for feature sections
When a section toggle has a side effect (persist immediately, warn first, tear down YAML on disable), prefer the healthcheck-style pattern: two branches — `<x-modal-confirmation>` for enable (with warning copy), `<x-forms.button wire:click>` for disable — instead of `<x-forms.checkbox wire:model.live>`. Matches existing visual language and makes the destructive step explicit. Reference: `resources/views/livewire/project/shared/health-checks.blade.php`.
