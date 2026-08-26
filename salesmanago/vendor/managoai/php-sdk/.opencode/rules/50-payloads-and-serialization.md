# Payloads And Serialization

- Treat payload builders, mappers, normalizers, and serializers as high-risk change points.
- Before editing, identify the exact source fields, transformation steps, and final output shape.
- Preserve existing keys, nesting, ordering expectations, and omission rules unless the task explicitly changes them.
- Be careful with empty strings, nulls, booleans, and numeric casting in outbound payloads.
- If a payload field is optional today, do not silently make it required without review.
- If a field moves between layers, confirm every consumer of the old shape.
- Add or update focused tests near the mapper, builder, or serializer when practical.
