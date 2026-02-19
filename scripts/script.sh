#!/bin/bash

# Define paths relative to the script location
# ../public/art/
BASE_DIR="$(dirname "$0")/../public/konst"
ORIGINAL_DIR="$BASE_DIR/original"

# 1. Setup: Create the subfolder structure inside public/art/
mkdir -p "$BASE_DIR/mini" "$BASE_DIR/thumb" "$BASE_DIR/medium" "$BASE_DIR/large"

# Enable case-insensitive matching
shopt -s nocaseglob

echo $ORIGINAL_DIR

# 2. Process: Loop through images in the public/art/original folder
for img in "$ORIGINAL_DIR"/*.{jpg,jpeg,png}; do
    [ -e "$img" ] || continue

    filename=$(basename "${img%.*}")

    # Get file size in bytes (Support for Linux and macOS)
    if [[ "$OSTYPE" == "darwin"* ]]; then
        filesize=$(stat -f%z "$img")
    else
        filesize=$(stat -c%s "$img")
    fi

    # 3. Quality Rules
    if [ "$filesize" -gt 3145728 ]; then
        QUALITY=43
    elif [ "$filesize" -gt 2097152 ]; then
        QUALITY=63
    elif [ "$filesize" -gt 1048576 ]; then
        QUALITY=73
    else
        QUALITY=87
    fi

    echo "Processing $filename (Quality: ${QUALITY}%)"

    # --- Generate the WebP versions ---
    # ROOT of /art/
    magick "$img" -auto-orient -quality "$QUALITY" "$BASE_DIR/${filename}.webp"

    # MINI: 70px height
    magick "$img" -auto-orient -resize x70 -quality "$QUALITY" "$BASE_DIR/mini/${filename}.webp"

    # THUMB: 140px height
    magick "$img" -auto-orient -resize x140 -quality "$QUALITY" "$BASE_DIR/thumb/${filename}.webp"

    # MEDIUM: 280px height
    magick "$img" -auto-orient -resize x280 -quality "$QUALITY" "$BASE_DIR/medium/${filename}.webp"

    # LARGE: 560px height
    magick "$img" -auto-orient -resize x560 -quality "$QUALITY" "$BASE_DIR/large/${filename}.webp"

done

shopt -u nocaseglob
echo "-------------------------------------------"
echo "Success! Images processed in $BASE_DIR"