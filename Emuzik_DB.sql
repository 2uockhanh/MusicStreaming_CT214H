CREATE DATABASE Emuzik_db;
use Emuzik_db;
create table Artists (
	Artist_id int PRIMARY KEY,
    Artist_name varchar(255) NOT NULL ,
    Biography text,
    Avatar_url varchar(500)
);
create table Albums (
	Album_id int PRIMARY KEY,
    Album_title varchar(255) NOT NULL,
    Release_date date,
    Cover_image_url varchar(500),
    Artist_id int references Artists(Artist_id)
);
create table Songs (
	Song_id int PRIMARY KEY,
    Song_title varchar(255) NOT NULL,
    File_url varchar(500) Not NULL,
    Lyric text, 
    View_count BIGINT,
    Album_id int references Albums(Album_id)
);
create table Genres (
	Genre_id int primary key,
    Genre_name varchar(255) not null
);
create table Songs_Genres (
	Song_id int,
    Genre_id int,
    foreign key (Song_id) references Songs(Song_id),
    foreign key (Genre_id) references Genres(Genre_id)
);
create table Users (
	User_id int primary key,
    User_name varchar(255) Not NULL,
    Email varchar(100) unique not null, 
    Password varchar(50) not null,
    User_avatar_url varchar(500),
    Role varchar(20) default 'user'
);
create table Playlists (
	Playlist_id int primary key,
    User_id int references Users (User_id),
    Playlist_name varchar(255) not Null,
    is_public TINYINT(1) default 1 	comment '1 :public , 0:privite'
);
create table playlist_Song (
	Playlist_id int ,
    Song_id int ,
    Added_at TIMESTAMP,
    foreign key (Playlist_id) references Playlists (Playlist_id),
    foreign key (Song_id) references Songs (Song_id)
);
create table Favorites (
	User_id int ,
    Song_id int,
    foreign key (User_id) references Users (User_id),
    foreign key (Song_id) references Songs (Song_id)
);
create table Follow (
	Follower_id int ,
    Followed_id		int,
    Created_at TIMESTAMP,
    foreign key (Follower_id) references Users(User_id),
    foreign key (Followed_id) references Artists(Artist_id)
);
    
