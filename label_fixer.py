from PIL import Image
import os
import pandas as pd
import argparse

'''
    Example usage: on the test set of OpenCXR-imagesorter project
         python label_fixer.py --path Y:projects/OpenCXR-imagesorter/processed_data/test_private.csv
         --data_dir Y:projects/OpenCXR-imagesorter/processed_data/test_png/
         --exclude_col 0 --image_column 1
    '''

# construct the argument parse and parse the arguments
ap = argparse.ArgumentParser()
ap.add_argument("--path", required=True,
	help="path of the csv file to import")

ap.add_argument("--data_dir", required=True,
	help="directory where images are stored")

ap.add_argument("--image_column", required=False,
	help="numerical column index where are path to images")
ap.set_defaults(image_column=0)

ap.add_argument("--full_path", required=False,
	help="if csv contains full path or only image name")
ap.set_defaults(full_path=False)

ap.add_argument("--exclude_col", required=False,
	help="column of no interest")
ap.set_defaults(exclude_col=None)


args = vars(ap.parse_args())


#Inputs

df_path = args["path"]

df_name = df_path.split('/')[-1]

img_column = int(args["image_column"])

full_path = args["full_path"]

exclude_col = int(args["exclude_col"])

data_dir = args["data_dir"]

thumb_dir = 'C:/xampp/htdocs/projects/label_fixer/thumbnails/'
if not os.path.exists(thumb_dir):
    os.makedirs(thumb_dir)


final_csv_path = 'C:/xampp/mysql/data/test/'+df_name


#read csv
df = pd.read_csv(df_path)

columns = df.columns

#take image column
imgs = df[columns[img_column]].values

labels_df = df.drop([columns[img_column]], axis=1)
if (exclude_col is not None):
    labels_df = labels_df.drop([columns[exclude_col]], axis=1)

img_path = []
size = (46,46)
print("creating thumbnails..")
for img in imgs:
    if not full_path:
        file = data_dir+img+'.png'
        img_name = img+'.png'
    else:
        file = img
        img_name = img.split('/')[-1]
    
    img_path.append('./thumbnails/'+img_name)
    im = Image.open(file)
    im.thumbnail(size)
    im.save(thumb_dir+img_name)
print('done.')

new_df = labels_df.copy()

new_df.insert(loc=0, column='Image', value=img_path)

new_df.to_csv(final_csv_path, index=False)