#!/bin/bash
# PostToolUse hook (Edit|Write|MultiEdit): append every file change to
# .claude/changelog/YYYY-MM-DD-<slug>.md  (slug from .claude/changelog/.current-slug)
root="${CLAUDE_PROJECT_DIR:-$(pwd)}"
dir="$root/.claude/changelog"
input=$(cat)
file=$(echo "$input" | jq -r '.tool_input.file_path // empty')
tool=$(echo "$input" | jq -r '.tool_name // empty')
[ -z "$file" ] && exit 0
case "$file" in "$dir"/*) exit 0 ;; esac   # don't log the log itself
rel="${file#$root/}"
slug=$(cat "$dir/.current-slug" 2>/dev/null | tr -d '[:space:]')
[ -z "$slug" ] && slug="general"
log="$dir/$(date +%F)-$slug.md"
if [ ! -f "$log" ]; then
  {
    echo "# $(date +%F)-$slug"
    echo
    echo "## Auto log (file changes)"
    echo
    echo "| Time | Tool | File |"
    echo "|------|------|------|"
  } > "$log"
fi
# keep the table contiguous: insert the row right after the last table line
echo "| $(date +%H:%M:%S) | $tool | $rel |" >> "$log"
exit 0
