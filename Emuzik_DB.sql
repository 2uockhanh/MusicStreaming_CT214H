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


-- ==========================================
-- THÊM DỮ LIỆU MẪU TỪ FILE HTML (Từng Ngày Yêu Em)
-- ==========================================

-- 1. Thêm nghệ sĩ (buitruonglinh)
INSERT INTO Artists (Artist_id, Artist_name, Biography, Avatar_url) 
VALUES (1, 'buitruonglinh', 'Nghệ sĩ Indie Việt Nam', './img/artist/artist_buitruonglinh.jpg');

-- 2. Thêm Album/Single (để liên kết với bài hát vì bảng Songs có tham chiếu tới Album_id)
INSERT INTO Albums (Album_id, Album_title, Release_date, Cover_image_url, Artist_id) 
VALUES (1, 'Từng Ngày Yêu Em (Single)', '2024-01-01', './img/song/song_buitruonglinh/song_tungngayyeuem/poster.jpg', 1);

-- 3. Thêm bài hát (Từng Ngày Yêu Em) kèm toàn bộ lời bài hát (Lyrics)
INSERT INTO Songs (Song_id, Song_title, File_url, Lyric, View_count, Album_id) 
VALUES (1, 'Từng Ngày Yêu Em', 'https://www.nhaccuatui.com/song/0uvhkrobLlp0', 'Lại chìm trong đôi mắt em xoe tròn ngất ngây\nPhút giây khi mà anh khẽ nhìn sang\nLại làm đôi môi nhớ em lại muốn hôn em thêm bao lần\nTừng ngày cô đơn xé đôi, hạ thu đông khẽ trôi\nCạnh bên em anh sẽ thôi u sầu\nLại làm cho anh càng thấy yêu em hơn ngày qua\nChẳng phải gió cuốn mưa bay đưa đôi tay \nĐón anh về ngày lời yêu cất lên\nChỉ cần thức giấc khi bên em nơi anh thấy yên bình\nBiển rộng sông sâu anh trót thương chỉ riêng mình em thôi đấy\nTình yêu ấy chẳng thể đổi thay\nDù là bao lâu cảm xúc trong anh mãi đong đầy\nVội lạc vào giấc mơ nhẹ nhàng tựa ý thơ\nNụ cười người để anh nhớ để anh mong đợi\nSợ một ngày ngát xanh cuộc đời này vắng em\nAnh phải làm sao bây giờ ?\nEm này anh biết chăng bao ngày bao tháng năm\nChỉ cần một lần say đắm sẽ in hằn sâu trong tim anh\nMãi như vậy như lời hứa yêu em yêu mình em một đời\nTừ khi yêu em thế gian vương đầy sắc hoa\nTới nơi chân trời ta vẫn hằng mơ\nĐừng để cho tia nắng mai đặt chiếc hôn lên đôi vai gầy\nMặc kệ cơn dông bước ra điều gì phía trước ta\nTừng ngày bên nhau lướt qua êm đềm\nLại làm cho anh càng muốn yêu em không rời xa\nTình yêu đến ngọt ngào yêu áng mây trên cao\nTình yêu khẽ thì thầm anh thích em ra sao\nTình yêu muốn nồng nàn như sóng xô dạt dào khắp muôn nơi\nTừng ngày cô đơn xé đôi hạ thu đông khẽ trôi\nCạnh bên em anh sẽ thôi u sầu\nLại làm cho anh càng thấy yêu em\nLại làm mình ngất ngây nghẹn ngào từng phút giây\nCả bầu trời ngày hôm ấy bỗng như thu lại\nNhẹ nhàng rồi dắt tay\nAnh ngỏ lời muốn em sẽ là của anh sau này\nEm à anh biết chăng\nDẫu là bao tháng năm\nChỉ cần một lần say đắm sẽ in hằn sâu trong tim anh\nMãi như vậy như lời hứa yêu em\nyêu mình em một đời\nTình yêu đến ngọt ngào yêu áng mây trên cao\nTình yêu khẽ thì thầm anh thích em ra sao\nTình yêu muốn nồng nàn như sóng xô dạt dào khắp muôn nơi\nTừng ngày cô đơn xé đôi hạ thu đông khẽ trôi\nCạnh bên em anh sẽ thôi u sầu\nLại làm cho anh càng thấy yêu em\nLại làm mình ngất ngây nghẹn ngào từng phút giây\nCả bầu trời ngày hôm ấy bỗng như thu lại\nNhẹ nhàng rồi dắt tay\nAnh ngỏ lời muốn em sẽ là của anh sau này\nEm à anh biết chăng\nDẫu là bao tháng năm\nChỉ cần một lần say đắm sẽ in hằn sâu trong tim anh\nMãi như vậy như lời hứa yêu em yêu mình em một đời\nLà ri la, là ri la, la ri la ta ta\nLà ri la, là ri la, la ri la ta ta\nLời yêu thương mà lâu nay\nTừ con tim anh dành cho em\nSẽ không bao giờ đổi thay\nLà ri la, là ri la, la ri la ta ta\nLà ri la, là ri la, la ri la ta ta\nVì anh sẽ mãi như vậy\nNhư lời hứa yêu em\nYêu mình em một đời', 0, 1);
