#Description: This is a program I created while researching Python. I had not used Python very much, so I used this to get used to the syntax, learn more about RegEx, and work with a command line inputs.
#Created by: Noah Guta

import re                                       #Import regex

fileName = input ("Enter file name (testFile.txt)")     #Asking for the file name to test

if len(fileName) == 0:
    fileName = "testFile.txt"                   #If nothing is inputted it'll set it to the file I created

file = open(fileName,"r")                       #Opens the file in read mode

line = file.readline()                          #Reads a line in the file

#It'll keep looping through until it reaches the end of file
while line:
    course = re.search('[A-Z]+\*[0-9]+',line)   #It returns an object
    if course != None:                          #Object will be None if it doesn't find anything
        print(course.string)                    #Otherwise object.string will print the whole line...which won't be what we want for the final product
    line = file.readline()

#In addition to these two programs, I also contributed to the wiki under Python