#!/bin/bash

# GitHub CLI command to create the security issue
# Use: bash github_issue_command.sh

gh issue create \
  --title "🚨 security: Fix critical CSRF token validation failures and form submission vulnerabilities" \
  --label "security,critical,bug,csrf,form-submission,biosecurity" \
  --body "$(cat CSRF_FORM_SUBMISSION_SECURITY_ISSUE.md)" \
  --assignee "" \
  --milestone ""

echo "GitHub issue created successfully!"
echo "Issue URL will be displayed above."