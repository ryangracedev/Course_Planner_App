import csv

#BMI stats
# Underweight = <18.5
# Normal weight = 18.5–24.9 
# Overweight = 25–29.9 
# Obesity = BMI of 30 or greater
class Student:
    def __init__(self,gender,start_weight,end_weight,BMI_start,BMI_end):
        self.gender = gender
        self.start_weight = start_weight
        self.end_weight = end_weight
        self.BMI_start = BMI_start
        self.BMI_end = BMI_end
    
def getBMIHealth(student_data):
    numNormal =0
    numUnderWeight = 0
    numObese = 0
    numOverweight = 0

    for student in student_data:
        if student.BMI_end < 18.5:
            numUnderWeight+=1
        elif 18.5<= student.BMI_end <=24.9:
            numNormal+=1
        elif 25<= student.BMI_end <=29.9:
            numOverweight+=1
        else:
            numObese+=1
    
    print("Number of individuals who were underweight in April: ", numUnderWeight)
    print("Number of individuals who were normal weight in April: ", numNormal)
    print("Number of individuals who were obese in April: ", numObese)
    print("Number of individuals who were overweight in April: ", numOverweight)

def avgWeightGain(student_data):

    x = 0
    for p in student_data:
        x+=p.end_weight - p.start_weight
    print(f"Average Weight Gain of Students was {round((x/len(student_data)),2)} lbs")

student_data = []
#reading the csv file 
with open('freshman_lbs.csv') as csv_file:
    csv_reader = csv.reader(csv_file, delimiter=',')
    line_count = 0
    for row in csv_reader:
        if line_count == 0:
            line_count += 1
        else:
            line_count += 1
            if len(row) == 0:
                break
            student_data.append(Student(row[0],int(row[1]),int(row[2]),float(row[3]),float(row[4])))

getBMIHealth(student_data)
avgWeightGain(student_data)

