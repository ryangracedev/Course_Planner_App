import csv

# Define the path to your CSV file
csv_file_path = '/home/undergrad/2/msadaqat/Desktop/python/pythonfile.csv'

with open('filetest.csv','r') as csv_file:

      reader = csv.reader(csv_file)

      for row in reader:
          print(row)
      csv_file.close()    

