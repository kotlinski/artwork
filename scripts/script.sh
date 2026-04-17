#!/bin/bash

# Define paths relative to the script location
# ../public/art/
BASE_DIR="$(dirname "$0")/../public/konst"
ORIGINAL_DIR="$BASE_DIR/original"

# 1. Setup: Create the subfolder structure inside public/konst/
mkdir -p "$BASE_DIR/square" "$BASE_DIR/square2x" "$BASE_DIR/thumb" "$BASE_DIR/thumb2x"

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

    # THUMB: fit within 122x122
    THUMB_Q=$(( QUALITY < 60 ? QUALITY : 60 ))
    convert "$img" -auto-orient -resize 122x122\> png:- | cwebp -q "$THUMB_Q" -o "$BASE_DIR/thumb/${filename}.webp" -- -

    # THUMB2X: fit within 244x244
    THUMB2X_Q=$(( QUALITY < 65 ? QUALITY : 65 ))
    convert "$img" -auto-orient -resize 244x244\> png:- | cwebp -q "$THUMB2X_Q" -o "$BASE_DIR/thumb2x/${filename}.webp" -- -

    # SQUARE: 122x122 center crop
    SQUARE_Q=$(( QUALITY < 65 ? QUALITY : 65 ))
    convert "$img" -auto-orient -resize 122x122^ -gravity center -extent 122x122 png:- | cwebp -q "$SQUARE_Q" -o "$BASE_DIR/square/${filename}.webp" -- -

    # SQUARE2X: 244x244 center crop
    SQUARE2X_Q=$(( QUALITY < 65 ? QUALITY : 65 ))
    convert "$img" -auto-orient -resize 244x244^ -gravity center -extent 244x244 png:- | cwebp -q "$SQUARE2X_Q" -o "$BASE_DIR/square2x/${filename}.webp" -- -

done

shopt -u nocaseglob
echo "-------------------------------------------"
echo "Success! Images processed in $BASE_DIR"