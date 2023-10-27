# Requirements Document


### 1. User Requirements

A Web-app to recored PGC flights in to the existing flight database. This is a Wordpress plugin and
is dependent on the CloudBase plugin. This module is also a bridge from the heritage PDP flight logging
system to Wordpress. As such is expected some code from the PDP system may be reused in this plugin. 
For example the Metrics page is expected to be reused with minor changes. 

It is expected when the PDP datebase is migrated into Wordpress, The multiple tables, one for each year, will be merged into one 
Wordpress table. 

##### 1.1 User Characteristics  
Users are members of the Philadelphia Glider Council Inc. It it assumed they will be instructed in the Web apps use by the Operation Staf, Field Manager or
other PGC members at the operations desk. 

##### 1.2 System's Functionality  

The plugin needs to provide a flight log interface, including an interface to allow editing flights after
a flight session is finished. A Metrics interface where flight metrics can be access by year for each;
glider, Tow Plane, Pilot, Instructor and Tow Pilot. In addition a "Personal Log page" where PGC members can 
review their flights for the current year. 

      
##### 1.3 User Interfaces   
Typically the web app is run on an tablet computer such as an iPad at the Operations Desk at the Flight Line. Editing of flights
might occur on any authorized members computer or tablet. Metrics and personal flight logs are typically viewed on a members home computer or tablet. 

### 2. System Requirements  


##### 2.1 Functional Requirements
*List here the functional requirements of the system. Functional requirements are requirements that specify __what__ the system should do and can be thought of as 'the system must do <requirement\>'. Implementation details for each requirement should be addressed in the system design document. An example of a functional requirement would be 'the system utilizes Java version...' This list can become quite extensive and for best practice each requirement should be issued its own unique name, number, and be accompanied by a description.*

#####2.2 Non-Functional Requirements
*List here the non-functional requirements of the system. Non-Functional requirements are requirements that specify __how__ the system should act and can be thought of as 'the system shall be <requirement\>'. An example of a non-functional requirement would be 'the system input should be able to handle any file smaller than...'*