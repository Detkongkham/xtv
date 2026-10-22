#!/bin/bash
# UserPromptSubmit hook: nudge Claude to load the ui-ux-pro-max skill for UI/UX prompts.
prompt=$(python3 -c 'import sys,json; print(json.load(sys.stdin).get("prompt",""))' 2>/dev/null)
if echo "$prompt" | grep -qiE 'ui|ux|design|layout|component|page|button|form|color|colour|font|typography|responsive|landing|dashboard|css|tailwind|style|ອອກແບບ|ໜ້າ|ສີ|ປຸ່ມ'; then
  echo "This task involves UI/UX. Invoke the ui-ux-pro-max skill (.claude/skills/ui-ux-pro-max) and use its search script (scripts/search.py) for design guidance before building or changing interfaces."
fi
exit 0
