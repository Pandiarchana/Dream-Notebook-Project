# Dream Notebook Project
## Introduction
  Dream Notebook is a web-based application designed to help users record their daily dreams or diary entries in a simple and meaningful way. Unlike traditional diary systems, this application goes beyond just storing text by integrating artificial intelligence to analyse user entries. The system identifies emotions, keywords, and recurring themes, allowing users to better understand their thoughts and patterns over time. This project is developed as part of a university group assignment and follows an iterative software development approach.

# Project Objective
  The main objective of the Dream Notebook project is to create a platform that not only allows users to write and store personal entries but also provides valuable insights through analysis. The system aims to improve self-awareness by transforming raw text into meaningful information. Additionally, the project focuses on building a scalable and structured web application while applying proper software engineering practices such as modular design and clean coding principles.

# Iteration 1 – Core Diary Functionality
  The first iteration of the project focuses on building a strong foundation by implementing the core diary functionality. During this stage, a console-based application was developed where users could create and view their diary or dream entries. These entries were stored using a simple file-based system instead of a database to keep the implementation straightforward. Basic error handling was included to ensure smooth execution. This iteration mainly focused on ensuring that the core functionality worked correctly before introducing more advanced features.

# Iteration 2 – Web System and Database Integration
  In the second iteration, the project was expanded from a console application into a web-based system. A user authentication system was introduced, allowing users to register, log in, and log out securely. Passwords were protected using hashing techniques to ensure security. The system was connected to a MySQL database, replacing the earlier file-based storage. Users could now create and view their entries through a web interface, making the application more interactive and user-friendly. This iteration marked a significant improvement in both functionality and system structure.

# Iteration 3 – AI Analysis and Data Visualisation
   The third iteration enhances the system by integrating artificial intelligence and data visualization features. The application uses AI to analyse user-written entries and extract emotions, keywords, and themes. These insights are then presented visually through charts such as bar graphs and line graphs, helping users identify emotional trends and recurring patterns over time. This transforms the application from a simple diary into an intelligent system that provides meaningful feedback and analysis.

# System Features
  The Dream Notebook system allows users to record their dreams or daily experiences in a structured format, with each entry automatically saved along with the date and time. The system includes AI-based analysis that processes the text to identify important emotional and thematic elements. Additionally, users can view their past entries along with the generated insights, enabling them to track changes and patterns. The data visualization feature further enhances user understanding by presenting information in a clear and visual manner.

# Code Quality and Improvements
  As part of the development process, a review of code quality was conducted using the principles of Single Responsibility Principle (SRP) and Don’t Repeat Yourself (DRY). It was observed that some files contained multiple responsibilities, such as handling validation, database operations, and output together. Improvements were planned to separate these concerns into reusable components. Repetition in session handling and validation logic was also identified, leading to the proposal of common reusable files such as authentication and validation modules. These improvements aim to make the system more maintainable and scalable.

# Project Tracking and Management
  The development process was managed using a GitHub Project Board, where tasks were organised into categories such as Todo, In Progress, and Done. This helped the team track progress effectively and ensure that all features were completed on time. Labels were also used to categorize tasks, improving clarity and workflow management. Regular updates were made to reflect the current state of the project.

# Conclusion
   The Dream Notebook project successfully demonstrates the transformation of a basic diary system into a more advanced and intelligent application. Through multiple iterations, the system evolved from simple text storage to a fully functional web application with AI-powered analysis and visualization features. The project highlights the importance of structured development, continuous improvement, and the application of software engineering principles in building scalable systems.
