--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: users; Type: TABLE; Schema: public; Owner: hi; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    lname character varying(255) NOT NULL,
    fname character varying(255) NOT NULL,
    email character varying(255),
    active boolean DEFAULT true
);


ALTER TABLE users OWNER TO hi;

--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: hi
--

INSERT INTO users VALUES (1, 'lname1', 'fname1', 'fname1@email.example', true);
INSERT INTO users VALUES (2, 'lname2', 'fname2', 'fname2@email.example', true);
INSERT INTO users VALUES (3, 'lname3', 'fname3', 'fname3@email.example', true);
INSERT INTO users VALUES (4, 'lname4', 'lname4', 'fname4@email.example', true);
INSERT INTO users VALUES (5, 'lname5', 'lname5', 'fname5@email.example', true);
INSERT INTO users VALUES (6, 'lname6', 'fname6', 'fname6@email.example', true);
INSERT INTO users VALUES (7, 'lname7', 'fname7', 'fname7@email.example', true);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: hi; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

