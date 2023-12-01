import csv
import re
from parser.course_parser import Course,print_course


#Find and return(last item) prerequisites for a course
def search(course_list, course_code, option):
    for x in course_list:                    
        if x.code == course_code:
            if option == 1:
                if len(x.prerequisites) == 0 or x.prerequisites[-1] is None:
                    print("This course has no prerequisites")
                    return
                else:
                    print("Prerequisites for your course: " + x.prerequisites[-1])
                    return
            else:
                print("Course Information\n===================")
                print_course(x)
                return
    print("ERROR: Could not find " + course_code)
        
    

#Parsing formatted CSV from the parser 
def parse_csv():
    courseList = []
    with open('parser/courses.csv', mode='r') as csv_file:    

        csv_reader = csv.DictReader(csv_file)    
        line_count = 0    
        
        for row in csv_reader:
            c = Course()

            c.prerequisites = []#changing prerequisites to list 
            c.code = row["code"]
            c.name = row["name"]
            c.availability = row["availability"]
            c.weight = row["weight"]
            c.description = row["description"]
            c.offerings = row["offerings"]
            c.equates = row["equates"]
            c.restrictions = row["restrictions"]
            c.departments = row["departments"]
            c.locations = row["locations"]
            c.prerequisites.append(row["prerequisites"])#adding first prerequisite

            #Adding remaining prerequisites if present
            if None in row:
                for i in row[None]:
                    c.prerequisites.append(i)
            
            courseList.append(c)   
    return courseList 

def CLI():
    courseList = parse_csv()
    print("========================\nWelcome to course CLI!\n========================\n")
    print("Menu\n====")
    print("1.Find prerequisites for a course")
    print("2.Find all course information")
    print("3.Exit Program")

    
    #regex pattern for matching course code
    pattern = r'[A-Za-z]{3,4}\*\d{4}$'

    while True:
        option = input("\nPlease enter an option: ")
        try:
            option = int(option)
        except ValueError:
            print("ERROR: Please enter an integer")
            continue

        if option == 1 or option == 2:
            course_code = input("\nPlease enter a course code (Example - CIS*3760): ").strip()
            match = re.match(pattern, course_code)
            if match is None: # fails to match pattern
                print("ERROR: Invalid course code format")
            else: 
                if option == 1:
                    search(courseList, course_code.upper(), 1)
                else:
                    search(courseList,course_code.upper(), 2)
        elif option == 3:
            break
        else:
            print("ERROR: Please enter 1, 2, or 3")
if __name__=='__main__':
    CLI()





