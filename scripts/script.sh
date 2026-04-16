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

    echo "Processing $filename (Base quality: ${QUALITY}%)"

    # --- Generate the WebP versions ---
    # Use convert for resize + auto-orient, then cwebp for efficient WebP encoding.
    # ImageMagick 6's WebP encoder produces bloated files; cwebp is ~8x smaller.

    # ROOT of /art/ (full-size WebP)
    convert "$img" -auto-orient png:- | cwebp -q "$QUALITY" -o "$BASE_DIR/${filename}.webp" -- -

    # MINI: 70px height
    MINI_Q=$(( QUALITY < 55 ? QUALITY : 55 ))
    convert "$img" -auto-orient -resize x70 png:- | cwebp -q "$MINI_Q" -o "$BASE_DIR/mini/${filename}.webp" -- -

    # THUMB: 140px height
    THUMB_Q=$(( QUALITY < 60 ? QUALITY : 60 ))
    convert "$img" -auto-orient -resize x140 png:- | cwebp -q "$THUMB_Q" -o "$BASE_DIR/thumb/${filename}.webp" -- -

    # MEDIUM: 280px height
    MEDIUM_Q=$(( QUALITY < 65 ? QUALITY : 65 ))
    convert "$img" -auto-orient -resize x280 png:- | cwebp -q "$MEDIUM_Q" -o "$BASE_DIR/medium/${filename}.webp" -- -

    # LARGE: 560px height
    LARGE_Q=$(( QUALITY < 75 ? QUALITY : 75 ))
    convert "$img" -auto-orient -resize x560 png:- | cwebp -q "$LARGE_Q" -o "$BASE_DIR/large/${filename}.webp" -- -

done

shopt -u nocaseglob
echo "-------------------------------------------"
echo "Success! Images processed in $BASE_DIR"