#!/bin/bash

# Inställningar
# Ändra till sökvägen där dina konverterade original-webp ligger
IMG_DIR="."
OUTPUT_SQL="final_dimension_update.sql"

# Töm SQL-filen
> "$OUTPUT_SQL"

echo "Läser av de faktiska måtten på bilderna i $IMG_DIR..."

# Kontrollera att mappen finns
if [ ! -d "$IMG_DIR" ]; then
    echo "FEL: Hittar inte mappen $IMG_DIR"
    exit 1
fi

# Loopa igenom alla webp-filer
for img in "$IMG_DIR"/*.webp; do
    if [ -f "$img" ]; then
        filename=$(basename "$img")

        # Hämta bredd och höjd direkt från den färdiga filen
        # Detta fångar upp om bilden roterats 90 grader
        width=$(webpinfo -summary "$img" | grep "Width:" | awk '{print $2}')
        height=$(webpinfo -summary "$img" | grep "Height:" | awk '{print $2}')

        if [[ $width =~ ^[0-9]+$ ]] && [[ $height =~ ^[0-9]+$ ]]; then
            echo "Match: $filename (${width}x${height}px)"
            # Skriv UPDATE-kommandot till filen
            echo "UPDATE images SET width_px=$width, height_px=$height WHERE file_name='$filename';" >> "$OUTPUT_SQL"
        else
            echo "Kunde inte läsa mått för: $filename"
        fi
    fi
done

echo "------------------------------------------------"
echo "KLART! Din nya SQL-fil är skapad: $OUTPUT_SQL"
echo "Kopiera innehållet och kör det i din MySQL-konsol."
