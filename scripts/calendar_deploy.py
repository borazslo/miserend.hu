import os
import shutil
import re

# A script fájl helye
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))

# Globális változók
# Relatív útvonalak a scripthez képest
FROM_PATH = os.path.join(SCRIPT_DIR, '..', 'calendar', 'dist', 'mcal', 'browser')
TO_PATH = os.path.join(SCRIPT_DIR, '..', 'webapp')

# A meglévő rendszer nem kezeli jól a reszponzív betűméretet, így a default méret szerint átkonvertálunk 
def process_css(source_file, destination_dir):
    # A fájl megnyitása és olvasása
    with open(source_file, 'r') as file:
        content = file.read()
    
    # A regex minta, amely a számokat és rem egységeket keres
    pattern = r'(\d*\.?\d+)rem'

    def convert(match):
        # Eredeti szám (pl. .75rem vagy 1.5rem)
        rem_value = float(match.group(1)) if match.group(1) else 0
        
        # Konvertálás px-be: 1rem = 16px
        px_value = rem_value * 16
        
        # Kerekítés egész számra
        rounded_px_value = round(px_value)
        
        # Visszatérünk az új px értékkel
        return f'{rounded_px_value}px'
    
    # Az összes rem egységet konvertáljuk
    new_content = re.sub(pattern, convert, content)
    
    # A bemeneti fájlnév feldolgozása a kimenethez
    file_name, file_extension = os.path.splitext(os.path.basename(source_file))
   
    output_file = os.path.join(destination_dir, f"{file_name}{file_extension}")

    
    try:
        print(f"[INFO] Save new file: {output_file}")
        with open(output_file, 'w') as file:
            file.write(new_content)
        print("[INFO] File saved successfully.")
    except PermissionError:
        print(f"[ERROR] You do not have permission to save the file: {output_file}")
    except Exception as e:
        print(f"[ERROR] Unknown error while saving file: {e}")

def find_file_by_pattern(path, pattern):
    for fname in os.listdir(path):
        if re.fullmatch(pattern, fname):
            return fname
    return None

def delete_file_by_pattern(path, pattern):
    for fname in os.listdir(path):
        if re.fullmatch(pattern, fname):
            os.remove(os.path.join(path, fname))
            print(f"Deleted: {fname}")

def update_layout_template(css_file):
    layout_path = os.path.join(TO_PATH, "templates", "layout.twig")
    with open(layout_path, "r", encoding="utf-8") as f:
        lines = f.readlines()

    updated_lines = []
    inside_head = False
    css_line_done = False

    i = 0
    while i < len(lines):
        line = lines[i]
        stripped = line.strip()
        updated_lines.append(line)

        if "<head" in stripped:
            inside_head = True

        if inside_head and "{# ### calendar-css ### #}" in stripped:
            # Marker megtalálva, nézzük a következő sort
            if i + 1 < len(lines) and 'href="/css/styles-' in lines[i + 1]:
                updated_lines.append(f'\t\t<link rel="stylesheet" href="/css/{css_file}">\n')
                i += 1  # átugorjuk az eredeti sort
            else:
                updated_lines.append(f'\t\t<link rel="stylesheet" href="/css/{css_file}">\n')
            css_line_done = True

        if inside_head and not css_line_done and "{% block extraHead %}{% endblock %}" in stripped:
            # Marker nincs, de extraHead blokk van → szúrjuk be
            updated_lines.insert(len(updated_lines) - 1, '\t\t{# ### calendar-css ### #}\n')
            updated_lines.insert(len(updated_lines) - 1, f'\t\t<link rel="stylesheet" href="/css/{css_file}">\n')
            updated_lines.insert(len(updated_lines) - 1, '\n')
            css_line_done = True

        if "</head>" in stripped:
            inside_head = False

        i += 1

    with open(layout_path, "w", encoding="utf-8") as f:
        f.writelines(updated_lines)

def replace_or_insert_calendar_app(template_path, main_js, polyfills_js):
    with open(template_path, "r", encoding="utf-8") as f:
        lines = f.readlines()

    filename = os.path.basename(template_path)
    new_lines = []
    inserted = False
    after_content_block = False

    app_html = (
        '{#calendar-app#}<mcal class="calendar-app">'
        '<app-root></app-root>'
        f'<script src="/js/mcal/{polyfills_js}" type="module"></script>'
        f'<script src="/js/mcal/{main_js}" type="module"></script></mcal>\n'
    )

    i = 0
    while i < len(lines):
        line = lines[i]
        stripped = line.strip()
        new_lines.append(line)

        # Megjegyezzük, ha már túl vagyunk a {% block content %} soron
        if re.match(r"{%\s*block\s+content\s*%}", stripped):
            after_content_block = True

            # Ha editschedule.twig vagy remark_list.twig fájlban vagyunk, akkor itt szúrjuk be közvetlenül
            if (filename == "editschedule.twig" or filename == "remark_list.twig" or "eventscatalogue.twig") and not inserted:
                if (i + 1) < len(lines) and lines[i + 1].strip().startswith("{#calendar-app#}"):
                    new_lines.append(app_html)
                    i += 1  # kihagyjuk a régit
                else:
                    new_lines.append(app_html)
                inserted = True

        elif filename == "church.twig" and after_content_block and re.match(r"{%\s*if\s+miseaktiv\s*==\s*1\s*%}", stripped):
            if not inserted:
                if (i + 1) < len(lines) and lines[i + 1].strip().startswith("{#calendar-app#}"):
                    new_lines.append(app_html)
                    i += 1
                else:
                    new_lines.append(app_html)
                inserted = True

        i += 1

    with open(template_path, "w", encoding="utf-8") as f:
        f.writelines(new_lines)

def update_home_twig(template_path, main_js, polyfills_js):
    with open(template_path, "r", encoding="utf-8") as f:
        lines = f.readlines()

    new_lines = []
    inserted = False

    app_html = (
        '{#calendar-app#}<mcal class="calendar-app">'
        '<app-root></app-root>'
        f'<script src="/js/mcal/{polyfills_js}" type="module"></script>'
        f'<script src="/js/mcal/{main_js}" type="module"></script></mcal>\n'
    )

    i = 0
    while i < len(lines):
        line = lines[i]
        stripped = line.strip()
        new_lines.append(line)

        if '<div class="miseurlap">' in stripped:
            if (i + 1) < len(lines) and lines[i + 1].strip().startswith("{#calendar-app#}"):
                new_lines.append(app_html)
                i += 1  # kihagyjuk a régi sort
            else:
                new_lines.append(app_html)
            inserted = True

        i += 1

    with open(template_path, "w", encoding="utf-8") as f:
        f.writelines(new_lines)

def main():
    # === CSS ===
    css_to_path = os.path.join(TO_PATH, "css")
    delete_file_by_pattern(css_to_path, r"styles-[a-zA-Z0-9]{8}\.css")

    css_file = find_file_by_pattern(FROM_PATH, r"styles-[a-zA-Z0-9]{8}\.css")
    if css_file:
        process_css(os.path.join(FROM_PATH, css_file), css_to_path)
        update_layout_template(css_file)
    else:
        print("The file styles-xxxxxxxx.css cannot be found!")

    # === JS+HTML ===
    js_to_path = os.path.join(TO_PATH, "js", "mcal")
    if os.path.exists(js_to_path):
        shutil.rmtree(js_to_path)
    os.makedirs(js_to_path, exist_ok=True)

    main_js = find_file_by_pattern(FROM_PATH, r"main-[a-zA-Z0-9]{8}\.js")
    polyfills_js = find_file_by_pattern(FROM_PATH, r"polyfills-[a-zA-Z0-9]{8}\.js")

    if main_js:
        shutil.copy(os.path.join(FROM_PATH, main_js), os.path.join(js_to_path, main_js))
    if polyfills_js:
        shutil.copy(os.path.join(FROM_PATH, polyfills_js), os.path.join(js_to_path, polyfills_js))

    if not (main_js and polyfills_js):
        print("The required JS file(s) could not be found!")
        return

    # church.twig => Sima naptár + javaslatbeküldő 2in1
    church_twig_path = os.path.join(TO_PATH, "templates", "church", "church.twig")
    replace_or_insert_calendar_app(church_twig_path, main_js, polyfills_js)

    # editschedule.twig => Védett szerkesztő
    editschedule_path = os.path.join(TO_PATH, "templates", "church", "editschedule.twig")
    replace_or_insert_calendar_app(editschedule_path, main_js, polyfills_js)
    
    # editschedule.twig => Javaslat elfogadó
    remark_list_path = os.path.join(TO_PATH, "templates", "remark_list.twig")
    replace_or_insert_calendar_app(remark_list_path, main_js, polyfills_js)
    
    # eventscatalogue.twig => időszak szerkesztő
    eventscatalogue_path = os.path.join(TO_PATH, "templates", "eventscatalogue.twig")
    replace_or_insert_calendar_app(eventscatalogue_path, main_js, polyfills_js)
    
    # home.twig
    # home_twig_path = os.path.join(TO_PATH, "templates", "home.twig")
    # update_home_twig(home_twig_path, main_js, polyfills_js)

    
    # === i18n fájl másolása ===
    i18n_src = os.path.join(FROM_PATH, "i18n", "hu.json")
    i18n_dst_dir = os.path.join(TO_PATH, "i18n")
    i18n_dst = os.path.join(i18n_dst_dir, "hu.json")
    
    if os.path.exists(i18n_src):
        os.makedirs(i18n_dst_dir, exist_ok=True)
        shutil.copy(i18n_src, i18n_dst)
    else:
        print("Source i18n/hu.json file not found!")


    # === cal_images mappa másolása ===
    cal_images_src = os.path.join(FROM_PATH, "cal_images")
    cal_images_dst = os.path.join(TO_PATH, "cal_images")

    if os.path.exists(cal_images_dst):
        shutil.rmtree(cal_images_dst)

    if os.path.exists(cal_images_src):
        shutil.copytree(cal_images_src, cal_images_dst)
        print(f"cal_images folder copied: {cal_images_dst}")
    else:
        print("Source cal_images folder not found!")


if __name__ == "__main__":
    print("[PYTHON] Deploy script starting...")
    main()
    print("[PYTHON] Deploy script finished [OK]")
