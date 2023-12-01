#Description: This is the second program I created while researching Python. I had not used Python very much, so I used this to get used to the syntax, learn more about working with CSV files, and work with a command line inputs.

#What it does: This script will open a premade csv file, take in the values, and then 

#Created by: Noah Guta

file = open("search.csv","r")               #Open file in read mode, csv should be hard coded in

#define the three arrays that will store the values
course = []
prereq = []
restrict = []

num = 0                                     #Num will keep track of how many lines are in the file, doing len(course) would also work

line = file.readline()

while line:
    values = line.split(',')                #This will split the line read from the csv, on the ",". It will then store it in an array
    course.append(values[0])                #Which is added to the main arrays here
    prereq.append(values[1])
    restrict.append(values[2])              #Note: one of these two cases should exist: every value that is empty still has something (I put "None" in my test) OR we check if the value is empty
    num += 1
    line = file.readline()

searchCourse = input("What course would you like to search for:")   # I did not allow multiple searches, but that can be done with a while and an exit case

for x in range(num - 1):                    #This for just searches for the course, assuming there's only one instance of it......
    if course[x] == searchCourse:
        print ("The Course: ",course[x])
        print("The Prerequisites: ", prereq[x])
        print("The Restrictions: ", restrict[x])
