#!/usr/bin/env python3
"""
Python script to create Unix-compatible ZIP file for DirectAdmin
"""

import os
import zipfile


def create_unix_zip():
    print("Creating Unix-compatible ZIP file...")

    # Define the project name and output file
    project_name = "halovpbank2025"
    output_file = f"{project_name}-final-unix.zip"

    # Remove existing ZIP if it exists
    if os.path.exists(output_file):
        os.remove(output_file)
        print("Removed existing ZIP file")

    # Files and folders to include
    include_items = [
        "admin",
        "api",
        "assets/css",
        "assets/js",
        "assets/images",
        "assets/fonts",
        "config-directadmin.php",
        "database-setup-directadmin.sql",
        "game.php",
        "index.php",
        "reward.php",
    ]

    print("Including files and folders:")
    for item in include_items:
        print(f"  - {item}")

    # Create ZIP file with Unix paths
    with zipfile.ZipFile(output_file, "w", zipfile.ZIP_DEFLATED) as zipf:
        for item in include_items:
            if os.path.exists(item):
                if os.path.isdir(item):
                    # Add directory recursively
                    for root, dirs, files in os.walk(item):
                        for file in files:
                            file_path = os.path.join(root, file)
                            # Convert Windows path to Unix path
                            arcname = file_path.replace("\\", "/")
                            zipf.write(file_path, arcname)
                            print(f"  Added: {arcname}")
                else:
                    # Add single file
                    arcname = item.replace("\\", "/")
                    zipf.write(item, arcname)
                    print(f"  Added: {arcname}")
            else:
                print(f"  Warning: {item} not found")

        # Add config.php (renamed from config-directadmin.php)
        if os.path.exists("config-directadmin.php"):
            zipf.write("config-directadmin.php", "config.php")
            print("  Added: config.php (renamed from config-directadmin.php)")

    # Get file size
    file_size = os.path.getsize(output_file)
    file_size_mb = round(file_size / (1024 * 1024), 2)

    print("\nUnix-compatible deployment package created successfully!")
    print(f"File: {output_file}")
    print(f"Size: {file_size_mb} MB")
    print("\nReady for DirectAdmin upload!")
    print("This ZIP uses Unix paths (forward slashes) for DirectAdmin compatibility.")


if __name__ == "__main__":
    create_unix_zip()
