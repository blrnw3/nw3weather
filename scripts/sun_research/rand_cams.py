import os
import shutil
import random
from datetime import datetime, time
from astral import LocationInfo
from astral.sun import sun
import pytz
from PIL import Image

LONDON = LocationInfo("London", "England", "Europe/London", 51.5074, -0.1278)

def is_daytime(filename, date_obj):
    try:
        base = os.path.basename(filename)
        if len(base) < 4 or not base[:4].isdigit():
            return False
        img_time = time(int(base[:2]), int(base[2:4]))

        s = sun(LONDON.observer, date=date_obj, tzinfo=pytz.timezone("Europe/London"))
        return s["sunrise"].time() <= img_time <= s["sunset"].time()
    except Exception:
        return False

def compress_jpeg(src_path, dest_path, quality=60):
    try:
        img = Image.open(src_path)
        img.save(dest_path, "JPEG", quality=quality, optimize=True)
    except Exception as e:
        print(f"Failed to compress {src_path}: {e}")

def sample_daytime_images(src_root, output_dir, sample_count, jpeg_quality=60):
    daytime_images = []

    for root, _, files in os.walk(src_root):
        parts = root.split('/')
        try:
            year, month, day = int(parts[-3]), int(parts[-2]), int(parts[-1])
            date_obj = datetime(year, month, day).date()
        except (ValueError, IndexError):
            continue

        for file in files:
            if file.lower().endswith(".jpg"):
                full_path = os.path.join(root, file)
                if is_daytime(file, date_obj):
                    date_str = f"{year:04d}{month:02d}{day:02d}"
                    daytime_images.append((full_path, date_str, file))

    if sample_count > len(daytime_images):
        print(f"Only {len(daytime_images)} daytime images available. Using all.")
        sample_count = len(daytime_images)

    sampled = random.sample(daytime_images, sample_count)
    os.makedirs(output_dir, exist_ok=True)

    for i, (full_path, date_str, original_name) in enumerate(sampled, 1):
        out_name = f"{date_str}_{original_name}"
        out_path = os.path.join(output_dir, out_name)
        compress_jpeg(full_path, out_path, quality=jpeg_quality)

        if i % 100 == 0 or i == sample_count:
            print(f"{i} images copied and compressed...")


    print(f"Copied and compressed {len(sampled)} images to {output_dir}")

if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description="Copy and compress N daytime images from 2023.")
    parser.add_argument("src", help="Root directory for 2023 image folders")
    parser.add_argument("dst", help="Destination directory for sampled images")
    parser.add_argument("--count", type=int, default=100, help="Number of images to sample (default: 100)")
    parser.add_argument("--quality", type=int, default=60, help="JPEG quality for output images (default: 60)")

    args = parser.parse_args()
    sample_daytime_images(args.src, args.dst, args.count, args.quality)
