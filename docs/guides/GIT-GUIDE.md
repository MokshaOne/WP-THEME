# Guide on Git

A practical reference for everyday Git work in this repository.

## 1. Getting Started

```bash
git clone <repo-url>        # copy a remote repository to your machine
git status                  # see what has changed
git log --oneline --graph   # compact history view
```

Set your identity once per machine:

```bash
git config --global user.name "Your Name"
git config --global user.email "you@example.com"
```

## 2. The Basic Workflow

```bash
git add <file>              # stage a specific file
git add .                   # stage everything in the current folder
git commit -m "Describe what changed and why"
git push -u origin <branch> # publish your branch (first push)
git push                    # subsequent pushes
```

Write commit messages in the imperative mood: "Add contact form", not "Added contact form".

## 3. Branching

```bash
git branch                        # list local branches
git checkout -b feature/my-work   # create and switch to a new branch
git switch main                   # switch back to main
git merge feature/my-work         # merge a branch into the current one
git branch -d feature/my-work     # delete a merged branch
```

Keep `main` deployable. Do all work on feature branches and merge via pull requests.

## 4. Staying Up to Date

```bash
git fetch origin                  # download remote changes without applying them
git pull origin main              # fetch and merge the remote main branch
git rebase origin/main            # replay your commits on top of main (clean history)
```

If a pull creates a merge conflict, Git marks the conflicting sections with
`<<<<<<<`, `=======`, and `>>>>>>>`. Edit the file to keep the correct version,
then:

```bash
git add <resolved-file>
git commit                        # or: git rebase --continue
```

## 5. Undoing Things

```bash
git restore <file>                # discard unstaged changes to a file
git restore --staged <file>       # unstage a file (keep the edits)
git commit --amend                # fix the last commit (message or content)
git revert <commit>               # safely undo a pushed commit with a new commit
git reset --hard <commit>         # DANGER: throw away commits and changes
```

Rule of thumb: once a commit is pushed and shared, use `revert`, never `reset`.

## 6. Stashing

Park work-in-progress without committing:

```bash
git stash                         # save and clean the working tree
git stash list                    # see saved stashes
git stash pop                     # re-apply the latest stash and drop it
```

## 7. Inspecting History

```bash
git diff                          # unstaged changes
git diff --staged                 # staged changes
git show <commit>                 # what a commit changed
git blame <file>                  # who last touched each line
```

## 8. Working with Remotes and Pull Requests

1. Create a feature branch: `git checkout -b feature/x`
2. Commit your work in small, logical steps.
3. Push: `git push -u origin feature/x`
4. Open a pull request on GitHub, describe the change, and request review.
5. After merge, delete the branch and pull the updated `main`.

## 9. Useful Extras

```bash
git tag v1.0.0                    # mark a release point
git cherry-pick <commit>          # copy one commit onto the current branch
git clean -nd                     # preview untracked files that would be removed
git reflog                        # recover "lost" commits
```

## 10. Quick Troubleshooting

| Problem | Fix |
|---|---|
| Committed to the wrong branch | `git checkout correct-branch && git cherry-pick <commit>` then remove it from the wrong branch |
| Push rejected (non-fast-forward) | `git pull --rebase origin <branch>` then push again |
| Accidentally staged a file | `git restore --staged <file>` |
| Need an old version of a file | `git checkout <commit> -- <file>` |
| Lost a commit after a reset | `git reflog` to find it, then `git checkout` or `git branch` from that hash |
