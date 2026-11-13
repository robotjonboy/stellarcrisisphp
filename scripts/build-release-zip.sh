#!/usr/bin/env bash
# Build a shared-hosting release zip for Stellar Crisis PHP.
# See docs/design-release-pipeline.md
set -euo pipefail

usage() {
	cat <<'EOF'
Usage: ./scripts/build-release-zip.sh [options]

  --tag <name>       Pack from git archive of <name> (preferred for production)
  --version <ver>    Override version string used in zip name / VERSION file
  --out <dir>        Output directory (default: dist)
  -h, --help         Show this help

Default (no --tag): pack from the current working tree with the same filters.
EOF
}

die() {
	echo "error: $*" >&2
	exit 1
}

warn() {
	echo "warning: $*" >&2
}

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_ROOT"

TAG=""
VERSION_OVERRIDE=""
OUT_DIR="$REPO_ROOT/dist"

while [[ $# -gt 0 ]]; do
	case "$1" in
		--tag)
			[[ $# -ge 2 ]] || die "--tag requires a value"
			TAG="$2"
			shift 2
			;;
		--version)
			[[ $# -ge 2 ]] || die "--version requires a value"
			VERSION_OVERRIDE="$2"
			shift 2
			;;
		--out)
			[[ $# -ge 2 ]] || die "--out requires a value"
			OUT_DIR="$2"
			shift 2
			;;
		-h|--help)
			usage
			exit 0
			;;
		*)
			die "unknown option: $1 (try --help)"
			;;
	esac
done

# Make --out absolute if relative
case "$OUT_DIR" in
	/*) ;;
	*) OUT_DIR="$REPO_ROOT/$OUT_DIR" ;;
esac

command -v zip >/dev/null || die "zip is required"
command -v rsync >/dev/null || die "rsync is required"

if [[ -n "$TAG" ]]; then
	command -v git >/dev/null || die "git is required for --tag"
	git rev-parse --git-dir >/dev/null 2>&1 || die "not a git repository"
	git rev-parse -q --verify "refs/tags/$TAG" >/dev/null \
		|| die "tag not found: $TAG"
fi

sanitize_version() {
	# Trim whitespace; spaces become hyphens; strip path separators.
	local v
	v="$(printf '%s' "$1" | tr -d '\r' | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' -e 's/[[:space:]]/-/g')"
	v="${v//\//}"
	v="${v//\\/}"
	[[ -n "$v" ]] || die "empty version string"
	printf '%s' "$v"
}

version_from_example() {
	local f="$1"
	[[ -f "$f" ]] || return 1
	sed -n "s/.*\$server\['version'\] *= *'\([^']*\)'.*/\1/p" "$f" | head -n1
}

resolve_version() {
	local v=""
	if [[ -n "$VERSION_OVERRIDE" ]]; then
		v="$VERSION_OVERRIDE"
	elif [[ -n "$TAG" ]]; then
		v="$TAG"
	elif command -v git >/dev/null && git rev-parse --git-dir >/dev/null 2>&1; then
		v="$(git describe --tags 2>/dev/null || true)"
	fi
	if [[ -z "$v" ]]; then
		v="$(version_from_example "$REPO_ROOT/serverconfig.php.example" || true)"
	fi
	if [[ -z "$v" ]]; then
		v="$(date -u +%Y%m%d)"
	fi
	sanitize_version "$v"
}

VERSION="$(resolve_version)"
TOP="stellar-crisis-php-${VERSION}"
ZIP_NAME="${TOP}.zip"
ZIP_PATH="${OUT_DIR}/${ZIP_NAME}"

STAGING_PARENT="$(mktemp -d "${TMPDIR:-/tmp}/sc-release.XXXXXX")"
cleanup() {
	rm -rf "$STAGING_PARENT"
}
trap cleanup EXIT

STAGE="$STAGING_PARENT/$TOP"
mkdir -p "$STAGE"

echo "Building release zip"
echo "  mode:    $([[ -n "$TAG" ]] && echo "tag ($TAG)" || echo "working-tree")"
echo "  version: $VERSION"
echo "  staging: $STAGE"

populate_from_tag() {
	# Extract tracked tree at tag, then prune with the same rules as working-tree.
	git archive --format=tar "$TAG" | tar -x -C "$STAGE"
}

populate_from_working_tree() {
	if [[ -f "$REPO_ROOT/serverconfig.php" ]]; then
		warn "serverconfig.php exists in working tree; it will be excluded from the zip"
	fi
	if [[ -f "$REPO_ROOT/.env" ]]; then
		warn ".env exists in working tree; it will be excluded from the zip"
	fi

	# Copy broadly, then prune. Excludes keep secrets and junk out of staging.
	# --no-owner/--no-group: staging must not require source uid/gid (e.g. history/ as nobody).
	rsync -a --no-owner --no-group \
		--exclude '.git/' \
		--exclude '.gitignore' \
		--exclude '.gitattributes' \
		--exclude '.cursor/' \
		--exclude '.project' \
		--exclude '.buildpath' \
		--exclude '.settings/' \
		--exclude 'serverconfig.php' \
		--exclude '.env' \
		--exclude '.env.example' \
		--exclude 'errorlog.html' \
		--exclude 'sql backups/' \
		--exclude 'docker/' \
		--exclude 'docker-compose.yml' \
		--exclude '.dockerignore' \
		--exclude 'dist/' \
		--exclude 'scripts/' \
		--exclude 'reference/' \
		--exclude 'phpversiondifferences.md' \
		--exclude '*.bak' \
		--exclude 'Thumbs.db' \
		--exclude 'WS_FTP.LOG' \
		--exclude 'Copy of *.php' \
		--exclude 'history/*.html' \
		--exclude 'images/aliens/custom/' \
		"$REPO_ROOT/" "$STAGE/"

	# Keep the custom-icon directory (e.g. .gitkeep). Never ship .gif uploads.
	mkdir -p "$STAGE/images/aliens/custom"
	if command -v git >/dev/null && git rev-parse --git-dir >/dev/null 2>&1; then
		while IFS= read -r -d '' rel; do
			case "$rel" in
				*.gif|*.GIF|*/Thumbs.db|*/WS_FTP.LOG) continue ;;
			esac
			[[ -e "$REPO_ROOT/$rel" ]] || continue
			mkdir -p "$STAGE/$(dirname "$rel")"
			cp -a "$REPO_ROOT/$rel" "$STAGE/$rel"
		done < <(git ls-files -z -- 'images/aliens/custom/')
	fi
}

prune_staging() {
	# Shared cleanup for both modes (tag archive includes tracked junk/docs).
	rm -rf \
		"$STAGE/.git" \
		"$STAGE/.cursor" \
		"$STAGE/.settings" \
		"$STAGE/docker" \
		"$STAGE/dist" \
		"$STAGE/scripts" \
		"$STAGE/reference" \
		"$STAGE/sql backups"

	rm -f \
		"$STAGE/.gitignore" \
		"$STAGE/.gitattributes" \
		"$STAGE/.project" \
		"$STAGE/.buildpath" \
		"$STAGE/.dockerignore" \
		"$STAGE/docker-compose.yml" \
		"$STAGE/serverconfig.php" \
		"$STAGE/.env" \
		"$STAGE/.env.example" \
		"$STAGE/errorlog.html" \
		"$STAGE/phpversiondifferences.md" \
		"$STAGE/Thumbs.db" \
		"$STAGE/WS_FTP.LOG"
	rm -f "$STAGE"/*.bak 2>/dev/null || true
	find "$STAGE" -type f -name 'Copy of *.php' -delete 2>/dev/null || true

	# History HTML must never ship (even if tracked in git).
	find "$STAGE/history" -type f -name '*.html' -delete 2>/dev/null || true

	# Custom empire icons are runtime uploads — never ship .gif from this folder.
	mkdir -p "$STAGE/images/aliens/custom"
	find "$STAGE/images/aliens/custom" -type f \( -iname '*.gif' \) -delete 2>/dev/null || true

	# Operator docs only under docs/
	if [[ -d "$STAGE/docs" ]]; then
		find "$STAGE/docs" -type f ! -name 'INSTALL.md' ! -name 'UPGRADE.md' ! -name 'shared-hosting-error-log.md' -delete
		find "$STAGE/docs" -type d -empty -delete 2>/dev/null || true
	fi
}

assert_required() {
	local missing=0
	local f
	for f in \
		sc.php \
		sc.sql \
		serverconfig.php.example \
		htaccess.example \
		LICENSE \
		utility \
		docs/INSTALL.md \
		docs/UPGRADE.md \
		docs/shared-hosting-error-log.md
	do
		if [[ ! -e "$STAGE/$f" ]]; then
			echo "error: required path missing from staging: $f" >&2
			missing=1
		fi
	done
	# At least one upgrade script if present in source; warn if none
	if ! compgen -G "$STAGE/upgrade_*.sql" >/dev/null; then
		warn "no upgrade_*.sql files in staging"
	fi
	[[ "$missing" -eq 0 ]] || die "staging incomplete (see above)"
}

assert_excludes() {
	local bad=0
	if [[ -e "$STAGE/serverconfig.php" ]]; then
		echo "error: staging contains serverconfig.php" >&2
		bad=1
	fi
	if [[ -e "$STAGE/.env" ]]; then
		echo "error: staging contains .env" >&2
		bad=1
	fi
	if [[ -d "$STAGE/.git" ]]; then
		echo "error: staging contains .git" >&2
		bad=1
	fi
	if [[ -d "$STAGE/docker" ]] || [[ -e "$STAGE/docker-compose.yml" ]]; then
		echo "error: staging contains docker packaging files" >&2
		bad=1
	fi
	if [[ -d "$STAGE/docs" ]] && compgen -G "$STAGE/docs/design-*.md" >/dev/null; then
		echo "error: staging contains design docs" >&2
		bad=1
	fi
	if find "$STAGE/history" -type f -name '*.html' 2>/dev/null | grep -q .; then
		echo "error: staging contains history/*.html" >&2
		bad=1
	fi
	if find "$STAGE/images/aliens/custom" -type f -iname '*.gif' 2>/dev/null | grep -q .; then
		echo "error: staging contains images/aliens/custom/*.gif" >&2
		bad=1
	fi
	[[ "$bad" -eq 0 ]] || die "forbidden paths present in staging"
}

write_version_file() {
	local commit="" built
	built="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
	if [[ -n "$TAG" ]]; then
		commit="$(git rev-list -n1 "$TAG" 2>/dev/null || true)"
	elif command -v git >/dev/null && git rev-parse --git-dir >/dev/null 2>&1; then
		commit="$(git rev-parse HEAD 2>/dev/null || true)"
	fi
	{
		echo "version=$VERSION"
		echo "built=$built"
		if [[ -n "$TAG" ]]; then
			echo "tag=$TAG"
		else
			echo "source=working-tree"
		fi
		if [[ -n "$commit" ]]; then
			echo "commit=$commit"
		fi
	} >"$STAGE/VERSION"
}

# --- assemble ---
if [[ -n "$TAG" ]]; then
	populate_from_tag
else
	populate_from_working_tree
fi
prune_staging
assert_required
assert_excludes
write_version_file

mkdir -p "$OUT_DIR"
rm -f "$ZIP_PATH"
(
	cd "$STAGING_PARENT"
	zip -rq "$ZIP_PATH" "$TOP"
)

echo
echo "Wrote $ZIP_PATH"
echo "  New install: see $TOP/docs/INSTALL.md inside the zip"
echo "  Update:      see $TOP/docs/UPGRADE.md inside the zip"
echo "  Prefer --tag for production hand-offs."
