Dim ie 
Set ie = CreateObject("InternetExplorer.Application")
'ie.Visible = True
ie.Navigate "http://192.168.0.162/Enertrade/pages/Telemedida/gestinel_autoupdate.php"
Do
Loop Until IE.readystate = 4
IE.Quit