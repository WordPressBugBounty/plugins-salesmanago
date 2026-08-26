---
description: Review shared configuration state, auth propagation, and test leakage risk
agent: config-state-reviewer
subtask: true
---

Review the configuration state impact for `$ARGUMENTS`.

Return:

1. Mutable state touched
2. Propagation path
3. Precedence or fallback risk
4. Test isolation risk
5. Safer reset or containment strategy
