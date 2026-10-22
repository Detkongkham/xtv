---
name: change-log
description: "Record what was changed in the project. Use at the start of a work phase/task to set the log name (e.g. phase1-a1-engine), and when finishing edits to write the summary: what changed, why, and whether it is ready to use. Logs live in .claude/changelog/YYYY-MM-DD-<phase>-<task>-<name>.md and are appended automatically by a PostToolUse hook."
---

# Change Log

File changes are logged automatically by `.claude/hooks/log-change.sh` into
`.claude/changelog/YYYY-MM-DD-<slug>.md` (example: `2026-10-05-phase1-a1-engine.md`).

## 1. Start of a task: set the slug
Write the slug (`phase<N>-<task id>-<name>`, lowercase, hyphens) to `.claude/changelog/.current-slug`:

    echo "phase1-a1-engine" > .claude/changelog/.current-slug

Change it whenever the phase/task changes. With no slug the hook uses `general`.

## 2. End of a task: write the summary
Append a section to today's log file (below the auto table), in this format:

    ## Summary
    - **What changed:** <files/features, one line each>
    - **Why:** <reason>
    - **Status:** <done | partial | blocked> — <can it be used/run now? how verified?>
    - **Next:** <what remains, if anything>

Be factual: state what was actually verified (ran, tested) vs. not.
