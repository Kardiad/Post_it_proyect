create table post_it(
	id int unsigned primary key auto_increment,
    header varchar(100),
    innertext text,
    size varchar(20),
    x int,
    y int,
    user int unsigned
);