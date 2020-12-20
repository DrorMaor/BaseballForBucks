
-- set up data tables
create table users (
    id int(6) unsigned auto_increment primary key,
    name varchar(50) not null
);

create table friends (
    id int(6) unsigned auto_increment primary key,
    name varchar(50) not null
);

create table UserFriends (
    id int(6) unsigned auto_increment primary key,
    user int,
    friend int
);

-- populate tables
insert into users (name) values ('Mr. Johnson');
insert into users (name) values ('Mr. Smith');
insert into users (name) values ('Mr. Williams');
insert into users (name) values ('Mr. Miller');

insert into friends (name) values ('Mr. White');
insert into friends (name) values ('Mr. Brown');
insert into friends (name) values ('Mr. Black');
insert into friends (name) values ('Mr. Green');

insert into UserFriends (user, friend) values (1, 1);
insert into UserFriends (user, friend) values (1, 2);
insert into UserFriends (user, friend) values (1, 4);
insert into UserFriends (user, friend) values (2, 1);
insert into UserFriends (user, friend) values (2, 2);
insert into UserFriends (user, friend) values (3, 3);

-- query, which user should befriend the other
select
    u1.name 'user', u2.name 'should_befriend', count(u1.id) 'common_friends'
from
    users u1 inner join UserFriends uf1 on uf1.user = u1.id,
    users u2 inner join UserFriends uf2 on uf2.user = u2.id
where uf1.friend = uf2.friend
    and u1.id <> u2.id
group by u1.id
order by count(u1.id) desc ;
