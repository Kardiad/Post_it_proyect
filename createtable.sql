create table post_it(
	id int unsigned primary key auto_increment,
    header varchar(100),
    innertext text,
    styles varchar(300),
    user int unsigned
)ENGINE=InnoDB COLLATE = utf8mb4_unicode_ci;
