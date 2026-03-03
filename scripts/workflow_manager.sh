#!/bin/bash

# Workflow Management Shell Script for Linux/Mac
# This provides easy access to workflow export/import tools

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."

show_menu() {
    clear
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║         BR_ERP Workflow Management Tool                   ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    echo "Current Directory: $(pwd)"
    echo ""
    echo "Choose an option:"
    echo "  1. Export workflows to file"
    echo "  2. Import workflows from file"
    echo "  3. Quick transfer wizard"
    echo "  4. View documentation"
    echo "  5. Exit"
    echo ""
    read -p "Enter your choice (1-5): " choice
    
    case $choice in
        1) export_workflows ;;
        2) import_workflows ;;
        3) quick_transfer ;;
        4) view_docs ;;
        5) exit 0 ;;
        *) show_menu ;;
    esac
}

export_workflows() {
    clear
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║                Export Workflows                            ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    read -p "Enter output filename (or press Enter for default): " filename
    
    if [ -z "$filename" ]; then
        php scripts/export_workflows.php
    else
        php scripts/export_workflows.php "$filename"
    fi
    
    echo ""
    read -p "Press Enter to continue..."
    show_menu
}

import_workflows() {
    clear
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║                Import Workflows                            ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    read -p "Enter input filename: " filename
    
    if [ -z "$filename" ]; then
        echo "Error: Filename required!"
        read -p "Press Enter to continue..."
        show_menu
        return
    fi
    
    if [ ! -f "$filename" ]; then
        echo "Error: File not found: $filename"
        read -p "Press Enter to continue..."
        show_menu
        return
    fi
    
    echo ""
    read -p "Overwrite existing workflows? (y/n): " overwrite
    
    if [ "$overwrite" = "y" ] || [ "$overwrite" = "Y" ]; then
        php scripts/import_workflows.php "$filename" --force
    else
        php scripts/import_workflows.php "$filename"
    fi
    
    echo ""
    read -p "Press Enter to continue..."
    show_menu
}

quick_transfer() {
    clear
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║            Quick Transfer Wizard                           ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    php scripts/quick_workflow_transfer.php
    read -p "Press Enter to continue..."
    show_menu
}

view_docs() {
    clear
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║                Documentation                               ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    
    if [ -f "scripts/WORKFLOW_EXPORT_IMPORT_GUIDE.md" ]; then
        if command -v less &> /dev/null; then
            less scripts/WORKFLOW_EXPORT_IMPORT_GUIDE.md
        else
            cat scripts/WORKFLOW_EXPORT_IMPORT_GUIDE.md
            echo ""
            read -p "Press Enter to continue..."
        fi
    else
        echo "Documentation file not found!"
        echo "Location: scripts/WORKFLOW_EXPORT_IMPORT_GUIDE.md"
        echo ""
        read -p "Press Enter to continue..."
    fi
    
    show_menu
}

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    exit 1
fi

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "Error: Please run this script from the BRERP root directory"
    exit 1
fi

# Start the menu
show_menu
