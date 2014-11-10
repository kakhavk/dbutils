--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


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


ALTER TABLE public.users OWNER TO hi;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: hi
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO hi;

--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: hi
--

COPY users (id, lname, fname, email, active) FROM stdin;
1	lname1	fname1	fname1@email.example	t
2	lname2	fname2	fname2@email.example	t
3	lname3	fname3	fname3@email.example	t
4	lname4	lname4	fname4@email.example	t
5	lname5	lname5	fname5@email.example	t
6	lname6	fname6	fname6@email.example	t
7	lname7	fname7	fname7@email.example	t
\.


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: hi
--

SELECT pg_catalog.setval('users_id_seq', 9, true);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: hi; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

