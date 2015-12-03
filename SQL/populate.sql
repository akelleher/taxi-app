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
