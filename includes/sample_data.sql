use Emuzik_db;
-- insert data into table Genres
insert into Genres (Genre_name) values
	('Ballad'), ('Pop') ,('Rap'), ('Rock'), ('EDM'), ('R&B'), ('Indie'); 
-- insert data into table Artist 
insert into Artists (Artist_name, Biography, Avatar_url)
	values ('Sơn Tùng M-TP','Nam ca sĩ, nhạc sĩ, diễn viên hàng đầu Việt Nam.','https://res.cloudinary.com/dmmfauvvu/image/upload/f_auto,q_auto/Sơn_Tùng_u6lhs8'),
		    ('Hieuthuhai','Nam rapper nổi tiếng ở Việt Nam, nổi lên từ King of Rap 2020 và là thành viên nhóm GERDNANG','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775808461/HieuThuHai_fioxai.jpg'),
			('Đen Vâu','Nam rapper nổi tiếng với phong cách âm nhạc mộc mạc và lời rap đậm chất tự xự','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775808718/%C4%90en_bzpuhg.jpg'),
			('Amee','Nữ ca sĩ sở hữu giọng hát ngọt ngào cùng vẻ ngoài xinh đẹp như idol Hàn Quốc','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775808805/Amee_ftbv7m.jpg'),
            ('Bùi Trường Linh','Sinh ngày 6 tháng 4 năm 1999 trong một gia đình gia giáo tại Hà Nội','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775808998/artist_buitruonglinh_t2pz92.jpg'),
            ('Lyly','Cô được mệnh danh là "Phù Thuỷ tạo hit" với khả năng sáng tác và giọng hát ngọt ngào','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775809140/Lyly_jelg2m.jpg'),
            ('Orange','Nữ ca sĩ kiêm nhạc sĩ Gen Z nổi tiếng với giọng hát nội lực, truyền cảm','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775809275/cam_cwwbha.jpg'),
            ('Quang Hùng Masterd','Ca sĩ, nhạc sĩ và nhà sản xuất âm nhạc tài năng','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775809493/Quang_H%C3%B9ng_wzlepn.jpg'),
            ('Dương Domic','Nam ca sĩ nổi bậc bước ra từ chương trình Anh Trai Say Hi','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775809641/Domic_swh0ts.jpg'),
            ('Hoà Minzy','Nữ ca sĩ nổi tiếng Việt Nam bước ra từ danh hiệu Quán quân học viện ngôi sao 2014','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775809774/Ho%C3%A0_rdatjq.jpg');
-- insert data into table Albums
insert into Albums (Album_title, Release_date, Cover_image_url, Artist_id) values
('Sky tour','2020-12-20','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775810472/Sky_tour_rbcssy.jpg',1),
('Ai cũng phải bắt đầu từ đâu đó','2023-10-16','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775810615/hieuthuhai_mxvoco.jpg',2),
('dongvui harmony','2022-11-09','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775810831/%C4%91ne_bi7y6i.jpg',3),
('dreAMEE','2020-06-28','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775810972/dreAmee_pwohq8.jpg',4);
-- insert data into table Songs
insert into Songs (Song_title, File_url, Song_image_url, Duration, Artist_id) values
('Hãy trao cho anh','/music/hay-trao-cho-anh.mp3','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775823226/hay-trao-cho-anh_yo2nhk.jpg',240,1),
('Giờ thì ai cười','/music/gio-thi-ai-cuoi.mp3','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775823623/gio-thi-ai-cuoi_gks1pm.jpg',185,2),
('Mười năm','/music/muoi-nam.mp3','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775823845/muoi-nam_yyazce.jpg',247,3),
('Hai mươi hai','/music/hai-muoi-hai.mp3','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775824088/hai_muoi_hai_gdoqfp.jpg',426,4),
('Từng ngày yêu em','/music/tung-ngay-yeu-em.mp3','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775824528/poster_lgypn7.jpg',225,5),
('24h','/music/24h.mp3','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775824752/24h_unnmwe.jpg',265,6),
('Gặp lại năm ta 60','/music/gap-lai-nam-ta-60.mp3','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775825041/gap-lai-nam-ta-60_fgln4f.jpg',345,7),
('Dễ đến dễ đi','/music/de-den-de-di.mp3','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775825309/de-den-de-di_bvtyba.jpg',251,8);
-- insert data into table Song_Genres
insert into Songs_Genres (Song_id, Genre_id) values
(1, 2), (1, 3), -- Hãy trao cho anh: Pop, Rap
(2, 3),         -- Giờ thì ai cười: Rap
(3, 3), (3, 7), -- Mười năm: Rap, Indie
(4, 1), (4, 2), -- Hai mươi hai: Ballad, Pop
(5, 1),         -- Từng ngày yêu em: Ballad
(6, 6),         -- 24h: R&B
(7, 1), (7, 7), -- Gặp lại năm ta 60: Ballad, Indie
(8, 2);         -- Dễ đến dễ đi: Pop
-- insert data into table Users 
insert into Users (User_name, Email, Password, User_avatar_url, Role) values
('Admin_Emuzik', 'admin@emuzik.com','admin@123','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775825891/admin_cjym0d.jpg','admin'),
('nbtoan','nbtoan@gmail.com','nguyenbaotoan','https://res.cloudinary.com/dmmfauvvu/image/upload/v1775825999/user1_ksdpif.jpg','user');
-- insert data into table Playlists
insert into Playlists (User_id, Playlist_name, Playlist_avatar_url, is_public) values
(2, 'Nhạc Chill Cuối Tuần', 'https://res.cloudinary.com/dmmfauvvu/image/upload/v1775810972/dreAmee_pwohq8.jpg', 1),
(2, 'Nhạc Cá Nhân Riêng Tư', NULL, 0);
-- insert data into playlist_song
-- Thêm các bài hát vào Playlist "Nhạc Chill Cuối Tuần" (ID 1)
insert into playlist_Song (Playlist_id, Song_id) values
(1, 4), -- Hai mươi hai
(1, 5), -- Từng ngày yêu em
(1, 7); -- Gặp lại năm ta 60
-- insert data into table Favorites
insert into Favorites (User_id, Song_id) values
(2, 1), -- Thích Hãy trao cho anh
(2, 3), -- Thích Mười năm
(2, 6); -- Thích 24h
-- insert data into table Follow
insert into Follow (Follower_id, Followed_id, Created_at) values
(2, 1, CURRENT_TIMESTAMP),
(2, 3, CURRENT_TIMESTAMP);