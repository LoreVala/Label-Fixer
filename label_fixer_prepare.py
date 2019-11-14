from PIL import Image
import os
import pandas as pd
import argparse
import json
import sys

'''
Preparation script to create all the files and images to launch label fixer web tool.
The script needs a json file in input where all the parameters are specified.

Required params:
--file : path to the initial json file 

Optional params:
--skip_thumb: do not create the thumbnails, only the fixed_ csv file and the web_params.json file will be created.
                e.g. the user already have the thumbnails.

--skip_output: do not create the fixed_ csv file, only the thumbnails and the web_params.json file will be created.
                e.g. the user want to change the thumbnails size but also don't want to loose the progress made.

'''

# construct the argument parse and parse the arguments
ap = argparse.ArgumentParser()
ap.add_argument('--file', required=True,
	help='path of the starter json file')

ap.add_argument('--skip_thumb', action='store_true',
                help='skip the creation of thumbnails')
ap.set_defaults(skip_thumb=False)

ap.add_argument('--skip_output', action='store_true',
                help='skip the creation of the output csv file')
ap.set_defaults(skip_output=False)

args = vars(ap.parse_args())

with open(args["file"]) as json_file:
    data = json.load(json_file)


df_path = data["init_csv_path"]
if os.path.isfile(df_path):
    df_name = os.path.basename(df_path)
else:
    sys.exit('Invalid parameter init_csv_path')
    
#read csv
df = pd.read_csv(df_path)

img_column = data["image_name_column"]
if img_column in df.columns:
    #take image column
    imgs = df[img_column].values
else:
    sys.exit('Invalid parameter image_name_column')


data_dir = data["png_data_dir"]
if not (os.path.isdir(data_dir) and os.path.isabs(data_dir)):
    sys.exit('Invalid parameter png_data_dir')

project_name = data["project_name"]
thumb_size = data["size_width"]
file_name = os.path.splitext(os.path.basename(df_path))[0]
thumb_dir = "C:\\xampp\\htdocs\\projects\\label_fixer\\thumbnails\\"
thumb_dir = os.path.join(thumb_dir, project_name)
thumb_dir = os.path.join(thumb_dir, file_name)
thumb_dir = os.path.join(thumb_dir, thumb_size)
os.makedirs(thumb_dir, exist_ok=True)

final_path = data["output_path"]
# check if path is correct
if os.path.isabs(final_path):
    os.makedirs(final_path, exist_ok=True)
else:
    sys.exit('Invalid parameter output_path')

is_valid = []
width = int(data["size_width"])
size = (width, width)
print(size)

if (not args["skip_thumb"]):
    print("Creating thumbnails at {} ".format(thumb_dir))
    # control variable
    name = 0
    path = 0
    for n, img in enumerate(imgs):
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

        #Progress bar
        sys.stdout.write('\r')
        sys.stdout.write("[%-20s] %d%%" % ('='* int((n*20)/len(imgs)), 5*int((n*20)/len(imgs))))
        sys.stdout.flush()
    print('done.')

else:
    print("Skip thumbnails creation.")
    for img in imgs:
        is_valid.append(1)


if not args["skip_output"]:    
    print('Creating csv file at {} '.format(os.path.join(final_path,"fixed_"+df_name)))
    new_df = df.copy()

    new_df["is_valid"] = is_valid

    new_df.to_csv(os.path.join(final_path,"fixed_"+df_name), index=False)
    print('done.')

else:
    print('Skip csv creation.')
    new_df = df.copy()
    new_df["is_valid"] = is_valid

print('Creating json file at {} '.format(os.path.join(final_path, "web_params.json")))
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

print('Visit http://localhost/projects/label_fixer/index.php and import the new json file to start the application.')