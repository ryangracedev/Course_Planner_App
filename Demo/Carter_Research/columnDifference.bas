Attribute VB_Name = "Module1"
' how to import code: go to VBA editor in excel and click file > import file
' Time spend researching documentation and debugging this ~3 hours
' what this does: select 2 columns and any number of rows of strings, and this will compare the length of string A to string B then output the interpreted results
' in the prompted output location.
Sub demo()
Attribute demo.VB_ProcData.VB_Invoke_Func = " \n14"

    ' Declares "rng" and "outputRng" as variables with type "Range"
    Dim rng As Range, outputRng As Range
    
    
    ' use "Set" when a variable needs to be set with a reference instead of value
    ' InputBox gets input from user, with a type 8 meaning it allows user to select the rows physically
    On Error GoTo EndOfSub
    Set rng = Application.InputBox("Select the 2 rows to compare", "User Prompt", Type:=8)
    Set outputRng = Application.InputBox("Select where to output results", "User Prompt", Type:=8)
    
    
    ' title the output columns
    ' Columns gets the column at provided index from the range referenced
    ' Rows does the same, but with rows
    outputRng.Columns(1).Rows(rng.Rows.Count + 1).Value = "String length difference type"
    outputRng.Columns(2).Rows(rng.Rows.Count + 1).Value = "Difference amount"

    
    Dim colA As Range, colB As Range, resCol As Range
    Set colA = rng.Columns(1)
    Set colB = rng.Columns(2)
    
    Dim x As Integer, y As Integer, compRes As Integer
    Dim cellA As Range, cellB As Range
    
    ' Column gets column INDEX
    x = rng.Columns(1).Column
    
    ' iterate from 1 to the last row input range
    For y = 1 To colA.Rows.Count
    
        ' gets left and right column cells
        Set cellA = rng.Cells(y, x)
        Set cellB = rng.Cells(y, x + 1)
    
        ' checks if string, if not GoTo end of loop
        If Not TypeName(cellA.Value) = "String" Or Not TypeName(cellB.Value) = "String" Then
            MsgBox ("input data is not a string")
            GoTo EndOfFor
        End If
        
        ' compares value string length
        compRes = Len(cellA.Value) - Len(cellB.Value)
        
        ' assign results to output cells
        If compRes < 0 Then
            outputRng.Columns(1).Rows(y).Value = "+"
            outputRng.Columns(2).Rows(y).Value = Math.Abs(compRes)
        ElseIf compRes > 0 Then
            outputRng.Columns(1).Rows(y).Value = "-"
            outputRng.Columns(2).Rows(y).Value = Math.Abs(compRes)
        Else
            outputRng.Columns(1).Rows(y).Value = "="
            outputRng.Columns(2).Rows(y).Value = 0
        End If
EndOfFor:
    Next y ' increment y
EndOfSub:
End Sub

