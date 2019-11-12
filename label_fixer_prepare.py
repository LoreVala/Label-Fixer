from PIL import Image
import os
import pandas as pd
import argparse
import json

# construct the argument parse and parse the arguments
ap = argparse.ArgumentParser()
ap.add_argument("--file", required=True,
	help="path of the starter json file ")

args = vars(ap.parse_args())

with open(args["file"]) as json_file:
    data = json.load(json_file)


df_path = data["init_csv_path"]
if os.path.isfile(df_path):
    df_name = os.path.basename(df_path)
else:
    sys.exit('Invalid paramaeter init_csv_file')
    
#read csv
df = pd.read_csv(df_path)

img_column = data["image_name_column"]
if img_column in df.columns:
    #take image column
    imgs = df[img_column].values
else:
    sys.exit('Invalid paramaeter image_name_column')


data_dir = data["png_data_dir"]
if not (os.path.isdir(data_dir) and os.path.isabs(data_dir)):
    sys.exit('Invalid paramaeter png_data_dir')


thumb_dir = data["thumbnail_out_dir"]
# check if path is correct
if os.path.isabs(thumb_dir):
    if (os.path.relpath(thumb_dir, start="C:\\xampp\\htdocs").find("..")  < 0):
        os.makedirs(thumb_dir, exist_ok=True)
    else:
        sys.exit('Invalid paramaeter thumbnail_out_dir')
else:
    thumb_dir = os.path.join("C:\\xampp\\htdocs", thumb_dir)
    os.makedirs(thumb_dir, exist_ok=True)

final_path = data["output_path"]
# check if path is correct
if os.path.isabs(final_path):
    os.makedirs(final_path, exist_ok=True)
else:
    sys.exit('Invalid paramaeter output_path')

img_path = []
is_valid = []
width = int(data["size_width"])
size = (width, width)
print(size)
print("creating thumbnails..")
# control variable
name = 0
path = 0
for img in imgs:
    if os.path.isfile(img): # check if in csv are stored full path or just names
        file = img
        img_name = os.path.basename(img)
        path += 1
    else:
        file = data_dir+img+'.png'
        img_name = img+'.png'
        name += 1
    #check if there are any irregularities
    if (path * name) != 0:
        sys.exit('process stopped because of irregularities in stored values for images')
    
    im = Image.open(file)
    im.thumbnail(size)
    im.save(os.path.join(thumb_dir, img_name))
    is_valid.append(1)

print('done.')
    
print('creating csv file...')
new_df = df.copy()

new_df["is_valid"] = is_valid

new_df.to_csv(os.path.join(final_path,"fixed_"+df_name), index=False)
print('done.')

print('creating json file...')
json_data = {}
json_data['path_to_csv_file'] = os.path.join(final_path,"fixed_"+df_name)
json_data['sql_path'] = "C:\\xampp\\mysql\\data\\test\\"
json_data['rel_path_to_thumb'] = os.path.relpath(path=thumb_dir, start="C:/xampp/htdocs/projects/label_fixer/")
json_data['image_column'] = img_column
json_data['columns'] = []
columns = new_df.columns
for col in columns:
    json_data['columns'].append(col)
json_data['editables_columns'] = data["editable_columns_and_labels"]
json_data['filterable_columns'] = data["filterable_columns_and_labels"]
with open(os.path.join(final_path, "web_params.json"), 'w') as outfile:
    json.dump(json_data, outfile, indent=4)

print('done.')
print('Json file created at ', os.path.join(final_path, "web_params.json"))
print('Visit http://localhost/projects/label_fixer/index.php and import the new json file to start the application.')