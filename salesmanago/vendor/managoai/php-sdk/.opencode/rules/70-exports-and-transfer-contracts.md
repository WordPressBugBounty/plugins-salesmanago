# Exports And Transfer Contracts

- Treat export-related arrays and transfer outputs as downstream contracts.
- Before editing export behavior, identify all builders, normalizers, and consumers involved.
- Preserve required fields, key names, and output grouping unless the task explicitly requires contract changes.
- Be explicit about whether a change affects contact exports, event transfers, batching, or result interpretation.
- Avoid mixing export contract changes with unrelated cleanup.
- If a change can affect external consumers, include a manual review note even if automated coverage is narrow.
