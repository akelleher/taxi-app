USE TA_Hunter;

/* USERS */
INSERT IGNORE INTO users (email, isAdmin, isDriver, isDispatcher, isfirstTime, firstName, lastName) VALUES ('mahonk3@rpi.edu',FALSE,TRUE,FALSE,FALSE,'Kevin','Mahon'); /* password = mahonk3 */
INSERT IGNORE INTO users (email, isAdmin, isDriver, isDispatcher, isfirstTime, firstName, lastName) VALUES ('Dispatcher@test.com',FALSE,FALSE,TRUE,FALSE,'Dispatcher_First','Dispatcher_Last'); /* password = testtesttest */
INSERT IGNORE INTO users (email, isAdmin, isDriver, isDispatcher, isfirstTime, firstName, lastName) VALUES ('admin@test.com',TRUE,FALSE,FALSE,FALSE,'admin','admin'); /* password = admin */
INSERT IGNORE INTO users (email, isAdmin, isDriver, isDispatcher, isfirstTime, firstName, lastName) VALUES ('allflags@test.com',TRUE,TRUE,TRUE,FALSE,'ALLFLAGS_FIRST','ALLFLAGS_LAST'); /* password = allflags */

/* PASSWORDS */
INSERT IGNORE INTO passwords(email, password) VALUES ('mahonk3@rpi.edu','$2y$10$KN0.2dx2J7ZCj1aPqhruwuZ7YR0soWcv/rWX7FuURB2U9EC5wUe2u');
INSERT IGNORE INTO passwords(email, password) VALUES ('Dispatcher@test.com','$2y$10$upW9J0/LbrG07GQYFWVYfuObnwX5TlRWHFx0YHgTpQqhNBI3bRmvK');
INSERT IGNORE INTO passwords(email, password) VALUES ('allflags@test.com','$2y$10$Bh3U8nyONCB28zcs8hHqL.YHQ65yhvk.h3MTpi/3PzYp6vSXndIYG');
INSERT IGNORE INTO passwords(email, password) VALUES ('admin@test.com','$2y$10$/TdhEef4181O.L2h26LEs.q0ZpOuLp8DyTcTBrg.EAVEi0XqHDzGC');

/* COURSES */
INSERT IGNORE INTO courses(name, subj, crse, Dispatcher_code) VALUES ( 'BASIC DRAWING', 'ARTS', 1200, '30aggahfngg4ccmp8engi3v3l75zs9m4x5m0ntfe5ouppd6cny' );
INSERT IGNORE INTO courses(name, subj, crse, Dispatcher_code) VALUES ( 'ART HISTORY I:FROM PALEOLITHIC TO RENAISSANCE', 'ARTS', 2530, '4wvro8maf69fyfebobv6zqmvhjnz50p9gnw0uwkvnze8xabuof' );
INSERT IGNORE INTO courses(name, subj, crse, Dispatcher_code) VALUES ( 'NETWORKING LABORATORY II', 'CSCI', 4660, 'tmtf2u0l9sut0q2xkscy1nal2mjqd9eubjmocd5uqlt9ao0nzu' );
INSERT IGNORE INTO courses(name, subj, crse, Dispatcher_code) VALUES ( '
WEB SYSTEMS DEVELOPMENT', 'ITWS', 2110, 'ip9yyrw4nmi42dzaishuckivkzov74kt8l6hbtldcu8mfzva03' );

/* DriverS_COURSES */
INSERT IGNORE INTO Drivers_courses(email, subj, crse) VALUES ('mahonk3@rpi.edu','ARTS',1200);
INSERT IGNORE INTO Drivers_courses(email, subj, crse) VALUES ('mahonk3@rpi.edu','ARTS',2530);
INSERT IGNORE INTO Drivers_courses(email, subj, crse) VALUES ('mahonk3@rpi.edu','CSCI',4660);
INSERT IGNORE INTO Drivers_courses(email, subj, crse) VALUES ('mahonk3@rpi.edu','ITWS',2110);

/* DispatcherS_COURSES */
INSERT IGNORE INTO Dispatchers_courses(email,subj,crse) VALUES ('Dispatcher@test.com','ARTS',1200);

/* Dispatcher_HOURS */
INSERT IGNORE INTO Dispatcher_hours(email, subj, crse, week_day, start_time, end_time) VALUES ('Dispatcher@test.com', 'ARTS', 1200, 'MONDAY', '16:00', '17:00');