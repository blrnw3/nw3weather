import cv2
import numpy as np
import os
import argparse

def is_sunny(image_path, brightness_thresh=130, blue_sky_thresh=0.3):
    img = cv2.imread(image_path)
    if img is None:
        return False  # Skip unreadable images
    hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)

    # Check brightness
    brightness = hsv[..., 2].mean()
    if brightness < brightness_thresh:
        return False

    # Check blue sky coverage
    lower_blue = np.array([90, 50, 50])
    upper_blue = np.array([130, 255, 255])
    mask = cv2.inRange(hsv, lower_blue, upper_blue)
    blue_ratio = np.sum(mask > 0) / mask.size

    return blue_ratio > blue_sky_thresh

def process_folder(image_dir, debug=False):
    sunny_minutes = 0
    total_images = 0

    for fname in sorted(os.listdir(image_dir)):
        if fname.lower().endswith((".jpg", ".jpeg", ".png")):
            path = os.path.join(image_dir, fname)
            sunny = is_sunny(path)
            if debug:
                print(f"{fname}: {'SUNNY' if sunny else 'NOT SUNNY'}")
            if sunny:
                sunny_minutes += 1
            total_images += 1

    sunny_hours = sunny_minutes / 60
    return sunny_hours, total_images

def main():
    parser = argparse.ArgumentParser(description="Estimate sunny hours from sky images.")
    parser.add_argument("directory", help="Path to directory of sky images")
    parser.add_argument("--debug", action="store_true", help="Print per-image classification")
    args = parser.parse_args()

    hours, total = process_folder(args.directory, args.debug)
    print(f"Sunny Hours: {hours:.2f} (out of {total / 60:.2f} possible hours)")

if __name__ == "__main__":
    main()
