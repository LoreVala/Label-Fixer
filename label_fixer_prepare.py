from PIL import Image
import os
import pandas as pd
import argparse
import json

# construct the argument parse and parse the arguments
ap = argparse.ArgumentParser()
ap.add_argument("--file", required=True,
	help="path of the json file ")

args = vars(ap.parse_args())

with open(args["file"]) as json_file:
    data = json.load(json_file)


df_path = data["init_csv_path"]
df_name = os.path.basename(df_path)

img_column = data["image_name_column"]

data_dir = data["png_data_dir"]

thumb_dir = data["thumbnail_out_dir"]
if not os.path.exists(thumb_dir):
    os.makedirs(thumb_dir)


final_path = data["output_path"]
os.makedirs(final_path, exist_ok=True)


#read csv
df = pd.read_csv(df_path)

#take image column
imgs = df[img_column].values

img_path = []
is_valid = []
width = int(data["size_width"])
size = (width, width)
print(size)
print("creating thumbnails..")

for img in imgs:
    if os.path.isabs(img):
        file = img
        img_name = os.path.basename(img)
        
    else:
        file = data_dir+img+'.png'
        img_name = img+'.png'
    
    img_path.append('./thumbnails/'+img_name)
    im = Image.open(file)
    im.thumbnail(size)
    im.save(thumb_dir+img_name)
    is_valid.append(1)

print('done.')

print('creating csv file...')
new_df = df.copy()

new_df.insert(loc=0, column='thumbnail_path', value=img_path)

new_df["is_valid"] = is_valid

new_df.to_csv(final_path+df_name, index=False)
print('done.')

print('creating json file...')
json_data = {}
json_data['csv_file'] = df_name
json_data['csv_name'] = os.path.splitext(df_name)[0]
json_data['output_path'] = final_path
json_data['sql_path'] = "C:/xampp/mysql/data/label_fixer/"
json_data['columns'] = []
columns = new_df.columns
for col in columns:
    json_data['columns'].append(col)
json_data['editables_columns'] = data["editable_columns_and_labels"]
json_data['filterable_columns'] = data["filterable_columns_and_labels"]
with open(final_path+os.path.splitext(df_name)[0]+".json", 'w') as outfile:
    json.dump(json_data, outfile, indent=4)

print('done.')
print('Json file created at ', final_path+os.path.splitext(df_name)[0]+".json")
print('Visit http://localhost/projects/label_fixer/index.php and import the new json file to start the application.')