#!/bin/bash

if [ -z "$1" ]; then
    echo "Error: Parameter is required"
    exit 1
fi

DOCKERFILE="containers/php/Dockerfile"

if [ ! -f "$DOCKERFILE" ]; then
    cp containers/php/Dockerfile.dist "$DOCKERFILE"
fi

# Get first line from Dockerfile and remove '#', spaces and newlines
first_line=$(head -n 1 "$DOCKERFILE" | sed 's/^#//;s/[[:space:]]//g')
input_param=$(echo "$1" | tr -d '[:space:]')

if [ "$first_line" != "$input_param" ]; then
    # Update first line in Dockerfile
    cp containers/php/Dockerfile.dist "$DOCKERFILE"

    # Detect OS and adjust sed command
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS: Use '' after -i
        sed -i '' "1s/.*$/# $input_param/" "$DOCKERFILE"
    else
        # Linux: No suffix needed
        sed -i "1s/.*$/# $input_param/" "$DOCKERFILE"
    fi

    echo "PHP changed"
    exit 0
else
    echo "PHP unchanged"
    exit 1
fi
