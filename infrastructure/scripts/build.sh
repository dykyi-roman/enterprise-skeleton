#!/bin/bash

if [ -z "$1" ]; then
    echo "Error: Parameter is required"
    exit 1
fi

DOCKERFILE="containers/php/Dockerfile"
DOCKERFILE_DIST="containers/php/Dockerfile.dist"

if [ ! -f "$DOCKERFILE_DIST" ]; then
    echo "Error: $DOCKERFILE_DIST not found"
    exit 1
fi

if [ ! -f "$DOCKERFILE" ]; then
    cp "$DOCKERFILE_DIST" "$DOCKERFILE"
fi

# Get first line from Dockerfile and remove '#', spaces and newlines
first_line=$(head -n 1 "$DOCKERFILE" | sed 's/^#//;s/[[:space:]]//g')
input_param=$(echo "$1" | tr -d '[:space:]')

if [ "$first_line" != "$input_param" ]; then
    # Update first line in Dockerfile
    cp "$DOCKERFILE_DIST" "$DOCKERFILE"

    # Detect OS and adjust sed command
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS: Use '' after -i
        sed -i '' "1s/.*$/# $input_param/" "$DOCKERFILE"
    else
        # Linux: No suffix needed
        sed -i "1s/.*$/# $input_param/" "$DOCKERFILE"
    fi

    echo "Configuration changed"

    # Process input parameters
    IFS=',' read -ra PARAMS <<< "$input_param"
    
    # Process each parameter
    for param in "${PARAMS[@]}"; do
        # Find line containing the parameter in a marker
        marker_line=$(grep -n "^# <<<.*${param}.*<<<$" "$DOCKERFILE" | cut -d: -f1)

        if [ -n "$marker_line" ]; then
            echo "Found section for $param"
            start_line=$marker_line
            
            # Get the line number of the next marker or end of file
            next_marker=$(tail -n +$((start_line + 1)) "$DOCKERFILE" | grep -n "# <<<" | head -1 | cut -d: -f1)
            if [ -n "$next_marker" ]; then
                end_line=$((start_line + next_marker - 1))
            else
                end_line=$(wc -l < "$DOCKERFILE")
            fi
            
            # Extract the content between markers
            content=$(sed -n "$((start_line + 1)),$((end_line - 1))p" "$DOCKERFILE")
            
            # Remove the '#' from the beginning of each line
            content_without_comments=$(echo "$content" | sed 's/^#//')
            
            # Create temporary file
            temp_file=$(mktemp)
            
            # Write the updated content
            sed -n "1,${start_line}p" "$DOCKERFILE" > "$temp_file"
            echo "$content_without_comments" >> "$temp_file"
            sed -n "$end_line,\$p" "$DOCKERFILE" >> "$temp_file"
            
            # Replace original file
            mv "$temp_file" "$DOCKERFILE"
        fi
    done

    exit 0
else
    echo "Configuration unchanged"
    exit 1
fi
