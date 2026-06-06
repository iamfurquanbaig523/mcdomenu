#!/usr/bin/env python3
"""Create a deterministic-ish zip archive from a directory."""

from pathlib import Path
import sys
import zipfile


def main() -> int:
    if len(sys.argv) != 3:
        print("Usage: zip-directory.py <source-dir> <artifact-path>", file=sys.stderr)
        return 2

    source_dir = Path(sys.argv[1]).resolve()
    artifact_path = Path(sys.argv[2]).resolve()

    if not source_dir.is_dir():
        print(f"Source directory does not exist: {source_dir}", file=sys.stderr)
        return 1

    artifact_path.parent.mkdir(parents=True, exist_ok=True)

    with zipfile.ZipFile(artifact_path, "w", compression=zipfile.ZIP_DEFLATED) as archive:
        for path in sorted(source_dir.rglob("*")):
            if path.is_dir():
                continue

            archive.write(path, path.relative_to(source_dir).as_posix())

    print(artifact_path)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
