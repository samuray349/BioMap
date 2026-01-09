--
-- PostgreSQL database dump
--

\restrict VTALrBZatkjjCYq5Q2A9pzZA37nyfIRU7YdYFDO0cRHTIdvuWdVMmBWXTg41dQS

-- Dumped from database version 17.7
-- Dumped by pg_dump version 17.7

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry and geography spatial types and functions';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: ameaca; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ameaca (
    ameaca_id integer NOT NULL,
    descricao text NOT NULL
);


--
-- Name: ameaca_ameaca_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.ameaca_ameaca_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: ameaca_ameaca_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.ameaca_ameaca_id_seq OWNED BY public.ameaca.ameaca_id;


--
-- Name: animal; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.animal (
    animal_id integer NOT NULL,
    nome_comum character varying(255) NOT NULL,
    nome_cientifico character varying(255) NOT NULL,
    descricao text NOT NULL,
    facto_interessante text NOT NULL,
    populacao_estimada integer NOT NULL,
    url_imagem character varying(255) NOT NULL,
    contagem_vistas integer DEFAULT 0,
    dieta_id integer NOT NULL,
    familia_id integer NOT NULL,
    estado_id integer NOT NULL
);


--
-- Name: animal_ameaca; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.animal_ameaca (
    animal_id integer NOT NULL,
    ameaca_id integer NOT NULL
);


--
-- Name: animal_animal_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.animal_animal_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: animal_animal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.animal_animal_id_seq OWNED BY public.animal.animal_id;


--
-- Name: avistamento; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.avistamento (
    avistamento_id integer NOT NULL,
    data_avistamento timestamp without time zone NOT NULL,
    "localização" public.geography(Point,4326) NOT NULL,
    animal_id integer NOT NULL,
    utilizador_id integer NOT NULL,
    data_criacao timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: avistamento_avistamento_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.avistamento_avistamento_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: avistamento_avistamento_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.avistamento_avistamento_id_seq OWNED BY public.avistamento.avistamento_id;


--
-- Name: dieta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dieta (
    dieta_id integer NOT NULL,
    nome_dieta text NOT NULL,
    hex_cor character varying(7) NOT NULL
);


--
-- Name: dieta_dieta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.dieta_dieta_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: dieta_dieta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.dieta_dieta_id_seq OWNED BY public.dieta.dieta_id;


--
-- Name: estado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.estado (
    estado_id integer NOT NULL,
    nome_estado character varying(50) NOT NULL,
    hex_cor character varying(7) NOT NULL
);


--
-- Name: estado_conservacao; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.estado_conservacao (
    estado_id integer NOT NULL,
    nome_estado character varying(100) NOT NULL,
    hex_cor character varying(7) NOT NULL
);


--
-- Name: estado_conservacao_estado_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.estado_conservacao_estado_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: estado_conservacao_estado_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.estado_conservacao_estado_id_seq OWNED BY public.estado_conservacao.estado_id;


--
-- Name: estado_estado_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.estado_estado_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: estado_estado_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.estado_estado_id_seq OWNED BY public.estado.estado_id;


--
-- Name: familia; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.familia (
    familia_id integer NOT NULL,
    nome_familia character varying(100) NOT NULL
);


--
-- Name: familia_familia_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.familia_familia_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: familia_familia_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.familia_familia_id_seq OWNED BY public.familia.familia_id;


--
-- Name: funcao; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.funcao (
    funcao_id integer NOT NULL,
    nome_funcao character varying(50) NOT NULL
);


--
-- Name: funcao_funcao_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.funcao_funcao_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: funcao_funcao_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.funcao_funcao_id_seq OWNED BY public.funcao.funcao_id;


--
-- Name: instituicao; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.instituicao (
    instituicao_id integer NOT NULL,
    nome character varying(255) NOT NULL,
    descricao text NOT NULL,
    localizacao_texto character varying(255) NOT NULL,
    telefone_contacto character varying(50) NOT NULL,
    url_imagem character varying(255) NOT NULL,
    "localização" public.geography(Point,4326) NOT NULL,
    dias_aberto character varying(255) NOT NULL,
    hora_abertura time without time zone NOT NULL,
    hora_fecho time without time zone NOT NULL
);


--
-- Name: instituicao_instituicao_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.instituicao_instituicao_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: instituicao_instituicao_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.instituicao_instituicao_id_seq OWNED BY public.instituicao.instituicao_id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    token_id integer NOT NULL,
    utilizador_id integer NOT NULL,
    token character varying(255) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    used boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: password_reset_tokens_token_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.password_reset_tokens_token_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: password_reset_tokens_token_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.password_reset_tokens_token_id_seq OWNED BY public.password_reset_tokens.token_id;


--
-- Name: utilizador; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.utilizador (
    utilizador_id integer NOT NULL,
    nome_utilizador character varying(100) NOT NULL,
    email character varying(255) NOT NULL,
    password_hash character varying(255) NOT NULL,
    funcao_id integer NOT NULL,
    estado_id integer NOT NULL,
    data_criacao timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: utilizador_utilizador_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.utilizador_utilizador_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: utilizador_utilizador_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.utilizador_utilizador_id_seq OWNED BY public.utilizador.utilizador_id;


--
-- Name: ameaca ameaca_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ameaca ALTER COLUMN ameaca_id SET DEFAULT nextval('public.ameaca_ameaca_id_seq'::regclass);


--
-- Name: animal animal_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal ALTER COLUMN animal_id SET DEFAULT nextval('public.animal_animal_id_seq'::regclass);


--
-- Name: avistamento avistamento_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.avistamento ALTER COLUMN avistamento_id SET DEFAULT nextval('public.avistamento_avistamento_id_seq'::regclass);


--
-- Name: dieta dieta_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dieta ALTER COLUMN dieta_id SET DEFAULT nextval('public.dieta_dieta_id_seq'::regclass);


--
-- Name: estado estado_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estado ALTER COLUMN estado_id SET DEFAULT nextval('public.estado_estado_id_seq'::regclass);


--
-- Name: estado_conservacao estado_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estado_conservacao ALTER COLUMN estado_id SET DEFAULT nextval('public.estado_conservacao_estado_id_seq'::regclass);


--
-- Name: familia familia_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.familia ALTER COLUMN familia_id SET DEFAULT nextval('public.familia_familia_id_seq'::regclass);


--
-- Name: funcao funcao_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcao ALTER COLUMN funcao_id SET DEFAULT nextval('public.funcao_funcao_id_seq'::regclass);


--
-- Name: instituicao instituicao_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.instituicao ALTER COLUMN instituicao_id SET DEFAULT nextval('public.instituicao_instituicao_id_seq'::regclass);


--
-- Name: password_reset_tokens token_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens ALTER COLUMN token_id SET DEFAULT nextval('public.password_reset_tokens_token_id_seq'::regclass);


--
-- Name: utilizador utilizador_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilizador ALTER COLUMN utilizador_id SET DEFAULT nextval('public.utilizador_utilizador_id_seq'::regclass);


--
-- Data for Name: ameaca; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.ameaca (ameaca_id, descricao) FROM stdin;
5	Perda e fragmentação do habitat devido à urbanização e agricultura intensiva
6	Redução das populações das suas principais presas por doenças
7	Atropelamentos em estradas
8	Caça ilegal e armadilhas destinadas a outros animais
9	Mudanças climáticas que afetam a disponibilidade de presas e habitat adequado
10	Conflito com a pecuária e outras atividades humanas
11	Doenças, Parasitas e Competição por recursos
12	asdasdasd
13	asdasdas
14	adadad
15	dasdasd
16	sadadad
17	asdadasda
18	sdasdadsa
19	adada
20	sdadsasdasd
21	dasdadad
22	adsfadsf
23	dsadsadassda
24	dsadsasdadsaasd
25	sad
26	bfdy5r4t rty6fhtfgryd
27	ola
28	bom
29	dia
30	isto
31	funciona
32	deffesdfedef
33	fefeefsdrefrdsefrd
34	eefrferefrdefdr
35	erfefrdfrdrfdsrsfd
36	efrdserferfdrfdrfd
37	erfsd
38	asdwsdasda
39	asdadadad
40	adsadada
41	dadadad
42	45tyt6uhr
43	tyhrgee
44	tthye
45	hyewgh
46	erwqh
47	234343e 3e3eqwsd24
48	3ewq4e3s dwse3 wdqq3ews2d
49	3wse3esw dqe3sw d3sewqd3s ewqd
50	3ews3e seqs 3weq
51	3s ewdq3se wqd3ewsq d43ews4 qd
52	556trtr65t6re456tre456tre
53	5t6re5t6re5t6re5t6re
54	5t66r5t6r5t6r
55	5t6r5t6r56tr5t6rt6r
56	55trtr65t6r4e5t6r5t6r
57	ewrfsrefsdesrdfwerfde
58	rfdswerfdswesrdwfeswrdferswdf
59	ersfwdersdfwerdfsw
60	erdfswerdsfwerdfswerdsfwerdfsw
61	erdfswerdfswerdfs
62	rftrfteretfdretdf
63	retfdrtefrtefdr
64	tefdrtfedrtefdrtefd
65	rtefdrtefdrtefdrtfe
66	rtefrtefdrtefd
67	primeiro
68	2
69	kerklmeklmeklme
70	Caçaaaaaa
71	DOisay54yre
72	tresgttrtre5t45y4
73	quatro5yryryr
74	cincotyryryy6r
75	Caçaaaaa
76	r,,ew,
77	,fldslfmsfdkmlklm
78	lmkdlmkdfsklmfdsmkl
79	lmklmlmkdfmklfdsmkl
80	lkdlmdsmklfds
81	a
82	aaaaanjfcgbh
83	546yrthfg
84	aaaaa67t8iyjgg
85	23w4resfd
86	grfdrfget
87	43rwe5tg5yhf
88	34rewtdf
89	756trytry5g
90	56tyr5 6t4reygd5try6
91	6yutj74ertdgf
92	45teryhr4etg5
93	45ertrwe4
94	w4ertret
95	retrreftgtr5
96	grtrtgtre4g5
97	asddasdsadas
98	dsasdadsa
99	sdasadsadsadsad
100	sdasdasdadsa
101	sdadsadasdasad
102	345er43
103	342424
104	342342434e3w
105	432432erw43
106	rer43wre43wfer43w
107	432w3e4rr4e3wd
108	re4w3erw34dwer34
109	ewr34dzswer43
110	wer43zdfsewr43z
111	er4w3zdszewr43dsf zewr34sd
112	234 rew3q2ew4
113	eq3swd2 aeq3wsd2a q3ew2sda
114	e3qwds2e3qw2dse3wq
115	2ds3qew2sde3qw2sd2sd
116	e3wdsq2e3qw2sda
117	234wred3we4rdfs
118	«'0+pok9ilkujy
119	4 53erdtf6yg7uh
120	56789i0oo'+p
121	'0+p9oiçkluk
122	Atropelamentos em estrada
123	34erre43xswerx43s
124	er43sxes4fres43fer
125	43fdser43fder4
126	ser43d ser43 s
127	er34d er434343d
128	34erre43xs
129	er43sxes4f
130	43fdse
131	ser43d
132	er34d
133	ameaca1
134	ameaca2
135	ameaca3
136	ameaca4
137	ameaca5
138	1231qe421
139	12543534435435
140	3121221
141	12345543453
142	12543453435
143	Caça furtiva
144	Perda de habitat
145	Conflito humano-animal
146	Diminuição de presas
147	Fragmentação populacional
148	Caça furtiva (marfim)
149	Expansão agrícola
150	Conflito com humanos
151	Secas/Clima
152	Bloqueio de rotas migratórias
153	Fragmentação de habitat
154	Baixa diversidade genética
155	Doenças (tuberculose bovina)
156	Conflito político em zonas de fronteira
157	Aquecimento dos oceanos (afasta os peixes)
158	Espécies invasoras nas ilhas (ratos)
159	Poluição por óleo
160	Redes de pesca
161	Turismo excessivo perto dos ninhos
162	Perturbação humana em grutas de reprodução
163	Emaranhamento em redes
164	Abate deliberado por pescadores
165	Doenças
166	Floração de algas tóxicas
167	Conflito com a pecuária
168	Caça legal e ilegal
169	Hibridação com cães
170	Fragmentação por autoestradas
171	Medo público
172	Alterações climáticas
173	Baixa densidade populacional
174	Perturbação humana
175	Pesticidas (matam o alimento)
176	Atropelamentos
177	Perda de jardins selvagens
178	Máquinas de cortar relva
179	Predadores domésticos
180	Caça excessiva
181	Colisões com veículos e comboios
182	Parasitas devido ao clima mais quente
183	Silvicultura intensiva
184	Predação por lobos/ursos
185	Conflito com agricultores (inundações)
186	Poluição da água
187	Barreiras artificiais (barragens)
188	Espécies invasoras (Castor-americano)
189	Tráfego rodoviário
190	Competição com o Vison-americano (espécie invasora)
191	Perda e degradação de zonas húmidas
192	Doenças (como a de Aleutian)
193	Doença fúngica (Bsal)
194	Destruição de florestas
195	Poluição de cursos de água
196	Comércio de animais
197	Asdasdasadsda
198	rraewqewweqw
199	sdasdasdasda
200	Poluição defrdsadsaa
201	efsewr
\.


--
-- Data for Name: animal; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.animal (animal_id, nome_comum, nome_cientifico, descricao, facto_interessante, populacao_estimada, url_imagem, contagem_vistas, dieta_id, familia_id, estado_id) FROM stdin;
4	Veado	Cervus elaphus	O veado-vermelho (Cervus elaphus) é o maior cervídeo da Península Ibérica. Apresenta um dimorfismo sexual acentuado, sendo os machos significativamente maiores e portadores de hastes (galhadas) ramificadas que caem e renovam-se anualmente. Ocupam zonas de bosque, matagais e áreas abertas.	Durante a época de acasalamento, no outono, ocorre a "brama". Os machos emitem bramidos potentes para atrair fêmeas e desafiar rivais, ecoando por quilómetros nas serras e montados onde habitam.	350000	animal/veado.jpg	0	2	3	7
2	Lobo Ibérico	Canis lupus signatus	O lobo-ibérico (\nCanis lupus signatus) é uma subespécie menor e mais esguia, endémica da Península Ibérica, caracterizada pela sua pelagem amarelo-acastanhada, focinho pontiagudo, olhos amarelados, e as distintas faixas negras nas pernas anteriores e uma mancha dorsal escura.	É uma espécie única da Península Ibérica, restrita ao Noroeste de Espanha e Norte de Portugal, com uma população em risco que exige conservação.	2500	animal/lobo_iberico.jpg	0	1	2	4
3	Águia Imperial	Aquila adalberti	A águia-imperial-ibérica (Aquila adalberti) é uma das aves de rapina mais raras do mundo e exclusiva da Península Ibérica. Possui uma plumagem castanha muito escura, com "ombros" brancos característicos na idade adulta. Habita zonas de montado e floresta esclerófila, necessitando de grandes árvores para nidificar.	Esta espécie é monogâmica e extremamente territorial. Os casais constroem ninhos enormes no topo de sobreiros ou azinheiras, que podem chegar a pesar centenas de quilos e são reutilizados e aumentados ano após ano.	800	animal/aguia_imp.jpg	0	1	4	5
1	Lince Ibérico	Lynx pardinus	O lince-ibérico é um felino de médio porte endêmico da Península Ibérica, considerado o felino mais ameaçado do mundo até recentemente. Reconhecido pelas suas orelhas pontiagudas com tufos pretos, patas longas, cauda curta e o característico “barbicho” facial, o lince-ibérico é um predador ágil e solitário, perfeitamente adaptado aos ecossistemas mediterrânicos.	Nos anos 2000, restavam menos de 100 indivíduos — à beira da extinção. Graças a programas de reprodução em cativeiro, reintrodução e proteção de habitat, hoje existem mais de 2.000 linces ibéricos em liberdade na Península Ibérica.	2000	animal/lince.jpg	0	1	1	4
38	Glutão	Gulo gulo	O maior mustelídeo terrestre. Parece um pequeno urso com cauda peluda. Vive nas regiões frias do norte da Europa (tundra/taiga)	Conhecido pela ferocidade desproporcional, capaz de enfrentar ursos e lobos por carcaças	2300	https://lucped.antrob.eu/public/animal/animal_695c4e9c20ccc6.03038751.png	0	3	15	5
39	Ouriço-cacheiro	Erinaceus europaeus	Pequeno mamífero noturno coberto de espinhos. Enrola-se numa bola defensiva quando ameaçado	Possui cerca de 5.000 a 7.000 espinhos que são pelos modificados com queratina	0	https://lucped.antrob.eu/public/animal/animal_695c4f13c5dfc7.76701576.png	0	3	32	7
32	Tigre	Panthera Tigris	O maior felino do mundo, conhecido pela pelagem laranja com riscas pretas. É um predador solitário e territorial	As riscas são como impressões digitais, únicas para cada indivíduo	37005500	https://lucped.antrob.eu/public/animal/animal_695c4bad8dd1c8.60039972.png	0	1	1	4
40	Alce	Alces alces	O maior membro da família dos veados. Tem um focinho longo e arqueado e uma "barba" de pele. Encontrado no Norte e Leste da Europa	Os machos perdem as hastes (cornos) todos os invernos e crescem novas na primavera; são excelentes nadadores	0	https://lucped.antrob.eu/public/animal/animal_695c4f87937552.00410037.png	0	2	3	7
33	Elefante-africano-da-savana	Loxodonta africana	O maior animal terrestre. Possui orelhas grandes em forma de África e uma tromba versátil usada para agarrar objetos	Têm o maior cérebro de qualquer animal terrestre e demonstram luto	415000	https://lucped.antrob.eu/public/animal/animal_695c4c39e92b14.54553845.png	0	2	9	4
34	Bisonte-europeu	Bison bonasus	O maior mamífero terrestre da Europa. É mais alto e menos corpulento que o seu "primo" americano	Foi extinto na natureza em 1927; todos os bisontes atuais descendem de apenas 12 indivíduos de zoológicos	7000	https://lucped.antrob.eu/public/animal/animal_695c4cb9bf6741.42315527.png	0	2	13	6
35	Papagaio-do-mar	Fratercula arctica	Ave marinha pequena e robusta, conhecida como "palhaço do mar" devido ao bico triangular laranja vivo e plumagem preta e branca	O bico colorido brilha apenas na época de acasalamento; no inverno, torna-se cinzento e menor	5773	https://lucped.antrob.eu/public/animal/animal_695c4d59348809.06498552.png	0	1	4	5
36	Foca-monge-do-mediterrâneo	Monachus monachus	Uma das focas mais raras do mundo. Vive em águas temperadas (Mediterrâneo e Madeira) e esconde as crias em grutas costeiras	Era tão confiante que os antigos gregos a consideravam protegida por Poseidon e Apolo; hoje é extremamente tímida	700	https://lucped.antrob.eu/public/animal/animal_695c4dcfd20649.89924910.png	0	1	5	4
41	Castor-europeu	Castor fiber	Grande roedor semiaquático com pelagem castanha impermeável e uma cauda larga e achatada usada como leme	São "engenheiros de ecossistemas"; as suas represas criam zonas húmidas vitais para peixes, aves e anfíbios	12	https://lucped.antrob.eu/public/animal/animal_695c5040b29d92.20549969.png	0	2	56	7
42	Vison-europeu	Mustela lutreola	É um pequeno carnívoro semiaquático com pelagem castanha escura e densa. É extremamente ágil na água e em terra, vivendo exclusivamente perto de rios e riachos de águas limpas. É uma das espécies de mamíferos mais raras do mundo	O Vison-europeu tem uma mancha branca distinta em ambos os lábios (superior e inferior), enquanto o Vison-americano geralmente só a tem no lábio inferior	5000	https://lucped.antrob.eu/public/animal/animal_695c50f68d2b22.42538089.png	0	1	15	3
\.


--
-- Data for Name: animal_ameaca; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.animal_ameaca (animal_id, ameaca_id) FROM stdin;
3	5
3	6
3	8
3	9
3	11
1	5
1	6
1	7
1	8
1	9
38	172
38	153
38	173
38	174
38	168
39	175
39	176
39	177
39	178
39	179
40	180
40	181
40	182
40	183
40	184
32	143
32	144
32	145
32	146
32	147
4	5
4	6
4	8
4	11
4	122
41	185
41	186
41	187
41	188
41	189
2	5
2	6
2	7
2	8
2	10
33	148
33	149
33	150
33	151
33	152
34	153
34	154
34	155
34	143
34	156
42	190
42	191
42	186
42	192
42	176
35	157
35	158
35	159
35	160
35	161
36	162
36	163
36	164
36	165
36	166
\.


--
-- Data for Name: avistamento; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.avistamento (avistamento_id, data_avistamento, "localização", animal_id, utilizador_id, data_criacao) FROM stdin;
65	2026-01-07 11:37:55.06	0101000020E610000072A43330F2FA21C026AB22DC64924340	3	5	2026-01-07 11:37:55.497385
66	2026-01-07 11:41:51.989	0101000020E6100000530438BD8B871FC06AA2CF4719894340	2	5	2026-01-07 11:41:53.952905
67	2026-01-07 11:41:59.567	0101000020E6100000B0928FDD050A21C04354E1CFF0244440	4	5	2026-01-07 11:42:01.519424
68	2026-01-08 09:29:41.776	0101000020E6100000221807978E9122C04A0D6D00368E4340	4	5	2026-01-08 09:29:42.800054
69	2026-01-08 09:29:50.717	0101000020E610000029B4ACFBC75222C0C7B94DB857884340	39	5	2026-01-08 09:29:51.741367
70	2026-01-08 09:30:05.022	0101000020E61000004703780B246022C0B476DB85E66A4340	39	5	2026-01-08 09:30:06.368762
\.


--
-- Data for Name: dieta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.dieta (dieta_id, nome_dieta, hex_cor) FROM stdin;
1	Carnívoro	#FF5733
2	Herbívoro	#33FF57
3	Omnívoro	#3357FF
\.


--
-- Data for Name: estado; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.estado (estado_id, nome_estado, hex_cor) FROM stdin;
1	Normal	#008000
2	Suspenso	#FFA500
3	Banido	#FF0000
\.


--
-- Data for Name: estado_conservacao; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.estado_conservacao (estado_id, nome_estado, hex_cor) FROM stdin;
1	Extinto	#000000
2	Extinto na Natureza 	#333333
6	Quase Ameaçada	#00FF00
7	Pouco Preocupante	#008000
8	Dados Insuficientes	#CCCCCC
9	Não Avaliada	#FFFFFF
5	Vulnerável	#FFCC00
4	Em Perigo	#FF6600
3	Perigo Crítico	#FF0000
\.


--
-- Data for Name: familia; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.familia (familia_id, nome_familia) FROM stdin;
1	Felidae
2	Canidae
3	Cervidae
4	Accipitridae
5	Phocidae
8	Ursidae
9	Elephantidae
10	Rhinocerotidae
11	Hominidae
13	Bovidae
14	Equidae
15	Mustelidae
16	Procyonidae
18	Delphinidae
19	Balaenopteridae
21	Strigidae
22	Falconidae
23	Psittacidae
24	Columbidae
25	Corvidae
26	Anatidae
27	Phasianidae
28	Spheniscidae
29	Picidae
30	Crocodylidae
31	Alligatoridae
32	Cheloniidae
33	Testudinidae
34	Pythonidae
35	Viperidae
36	Colubridae
37	Gekkonidae
38	Iguanidae
39	Chamaeleonidae
40	Ranidae
41	Bufonidae
42	Salamandridae
43	Dendrobatidae
44	Salmonidae
45	Carcharhinidae
46	Lamnidae
47	Scombridae
48	Cyprinidae
49	Cichlidae
50	Formicidae
51	Apidae
52	Papilionidae
53	Nymphalidae
54	Scarabaeidae
55	Octopodidae
56	Castoridae
\.


--
-- Data for Name: funcao; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.funcao (funcao_id, nome_funcao) FROM stdin;
1	Admin
2	Utilizador
\.


--
-- Data for Name: instituicao; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.instituicao (instituicao_id, nome, descricao, localizacao_texto, telefone_contacto, url_imagem, "localização", dias_aberto, hora_abertura, hora_fecho) FROM stdin;
14	Observatório das Asas Livres	Uma instituição especializada na proteção de aves migratórias que cruzam os continentes. O Observatório mantém "corredores de repouso" — áreas protegidas onde as aves podem alimentar-se e descansar sem interferência urbana. O seu principal projeto atual é o combate à poluição luminosa, que desorienta milhares de aves anualmente durante as suas rotas noturnas.	Sintra, Lisboa - Portugal	voo@asaslivres.pt | +351 220 123 456	https://lucped.antrob.eu/public/instituicao/instituicao_695abc51bfb1d1.62315464.png	0101000020E610000032D010E942AF22C0AED2E17E6C6D4340	Segunda, Terça, Quarta, Quinta, Sexta, Sábado, Domingo	09:00:00	18:00:00
16	Fundação Lobo da Estrela	Esta fundação dedica-se à preservação do Lobo Ibérico no maciço central da Serra da Estrela. O seu trabalho principal é a mediação entre pastores e a fauna selvagem, fornecendo cães de gado (Cão da Serra da Estrela) e vedações eletrificadas para reduzir o conflito. Possuem também um centro de interpretação onde utilizam realidade virtual para educar sobre a importância do predador no ecossistema de montanha.	Covilhã, Castelo Branco - Portugal	+351 275 800 200 | alcateia@loboestrela.org	https://lucped.antrob.eu/public/instituicao/instituicao_695abf1bb19e38.66873283.png	0101000020E610000071348D76F85F1EC000EF058AB0274440	Segunda, Terça, Quarta, Quinta, Sexta	09:00:00	17:00:00
13	ICNF - Instituto da Conservação da Natureza e das Florestas	O Centro de Conservação do Lince Ibérico é uma instituição respeitada no campo de conservação animal.	Lourinhã, Lisboa - Portugal	(351) 213 507 900	https://lucped.antrob.eu/public/instituicao/instituicao_695abb0a34de27.24643263.png	0101000020E610000032D010E9226622C0EBEB563C4D9B4340	Segunda, Terça, Quarta, Quinta, Sexta	09:00:00	18:00:00
21	Reserva dos Cavalos do Vento	Localizada no Parque Nacional da Peneda-Gerês, esta reserva foca-se na proteção do Garrano, o cavalo selvagem autóctone de Portugal. O projeto utiliza tecnologia de "pastoreio virtual" para manter os cavalos em áreas seguras, longe de estradas, enquanto ajudam na prevenção de incêndios ao limpar a vegetação rasteira.	Ponte de Sor, Portalegre - Portugal	+351 253 111 222 | geral@cavalosdovento.pt	https://lucped.antrob.eu/public/instituicao/instituicao_695ac50927e6f7.49339010.png	0101000020E61000005AA021D265A21FC0B0FEEFE68AAA4340	Segunda, Terça, Quarta, Quinta, Sexta	09:00:00	17:00:00
22	Santuário Quiróptero de Alqueva	Focada na conservação de várias espécies de morcegos que habitam as fendas e ruínas perto da barragem do Alqueva. A instituição promove o uso de morcegos como controladores naturais de pragas agrícolas, instalando "hotéis de morcegos" em vinhas e olivais orgânicos para reduzir o uso de pesticidas.	Mértola, Beja - Portugal	+351 285 999 000 | noite@morcegosalqueva.pt	https://lucped.antrob.eu/public/instituicao/instituicao_695ac5729ce6c9.10274282.png	0101000020E6100000BFDD7C350CD71EC054CADE3D7CE34240	Segunda, Terça, Quarta, Quinta, Domingo	09:00:00	19:00:00
24	Centro de conservação	Centro de converação 2	Santarém - Portugal	938444444	https://lucped.antrob.eu/public/instituicao/instituicao_695ce72e90b3e7.27811458.webp	0101000020E610000013020352AC6421C0BA55C675429E4340	Segunda, Terça, Quarta, Quinta	08:00:00	17:10:00
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (token_id, utilizador_id, token, expires_at, used, created_at) FROM stdin;
\.


--
-- Data for Name: spatial_ref_sys; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.spatial_ref_sys (srid, auth_name, auth_srid, srtext, proj4text) FROM stdin;
\.


--
-- Data for Name: utilizador; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.utilizador (utilizador_id, nome_utilizador, email, password_hash, funcao_id, estado_id, data_criacao) FROM stdin;
20	Utilizador	email3@email.com	f650ca5028b2d64b042c70afd863022d552d43d27fbd2d33d953ac106239e839	2	1	2025-12-23 19:40:08.011369
4	Lucas	email@email.com	6800473f1396b92f55a86850af1422a3079fbf279961b44d8f2e55a3b68bb760	1	1	2025-12-11 10:04:30.190098
5	paula222	paula222@gmail.com	df39d3b3dd7dd59a7738e2c81ebb964b0349e695e49df6a6799f84a0999cc2bb	1	1	2025-12-12 11:40:34.775119
18	Pedro 41	pedrovan41@gmail.com	2659d77a793141e6ce2af5196a2020bc67c6361cab1602ee4a154e0af51ee90d	2	1	2025-12-21 19:14:43.155021
13	Pinheiro	goncalomldp.02@gmail.com	657fa3ea317daed6d6edfd8d4972be70793c4ab199281f8bfcdb67e93747c502	2	3	2025-12-18 22:03:26.560434
7	antrob	a.roberto08@gmail.com	8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92	1	1	2025-12-18 11:48:48.475671
9	arroz	arroz@email.com	6ba3f7dd611ef81b0d94f14c4f196f4e7b17cc83b6b62e858271115fe35be7c6	1	1	2025-12-18 12:08:45.571969
26	paula444	240000914@esg.ipsantarem.pt	b8590bbae9ddd0210ce2bdd8db925a57d2f21fd82ce7f0a3ebfddc567b95cfc3	2	1	2026-01-06 10:27:27.369488
25	Carlos	carlos@email.com	f650ca5028b2d64b042c70afd863022d552d43d27fbd2d33d953ac106239e839	2	3	2026-01-06 09:45:05.159793
27	paula555	paula555@gmail.com	23d9fc2472e474c096058074a7247d5266a38e63ad18406875b6b40dd3729cc6	2	1	2026-01-08 10:09:35.146999
10	32443432	paula111@gmail.com	823ec9109515e17c402b1aadf8c16aa270c6f2895023cf026d966a5c70bc6373	2	1	2025-12-18 20:06:59.840544
\.


--
-- Name: ameaca_ameaca_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.ameaca_ameaca_id_seq', 201, true);


--
-- Name: animal_animal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.animal_animal_id_seq', 46, true);


--
-- Name: avistamento_avistamento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.avistamento_avistamento_id_seq', 71, true);


--
-- Name: dieta_dieta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.dieta_dieta_id_seq', 3, true);


--
-- Name: estado_conservacao_estado_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.estado_conservacao_estado_id_seq', 9, true);


--
-- Name: estado_estado_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.estado_estado_id_seq', 3, true);


--
-- Name: familia_familia_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.familia_familia_id_seq', 56, true);


--
-- Name: funcao_funcao_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.funcao_funcao_id_seq', 3, true);


--
-- Name: instituicao_instituicao_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.instituicao_instituicao_id_seq', 24, true);


--
-- Name: password_reset_tokens_token_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.password_reset_tokens_token_id_seq', 4, true);


--
-- Name: utilizador_utilizador_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.utilizador_utilizador_id_seq', 27, true);


--
-- Name: ameaca ameaca_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ameaca
    ADD CONSTRAINT ameaca_pkey PRIMARY KEY (ameaca_id);


--
-- Name: animal_ameaca animal_ameaca_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal_ameaca
    ADD CONSTRAINT animal_ameaca_pkey PRIMARY KEY (animal_id, ameaca_id);


--
-- Name: animal animal_nome_cientifico_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal
    ADD CONSTRAINT animal_nome_cientifico_key UNIQUE (nome_cientifico);


--
-- Name: animal animal_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal
    ADD CONSTRAINT animal_pkey PRIMARY KEY (animal_id);


--
-- Name: avistamento avistamento_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.avistamento
    ADD CONSTRAINT avistamento_pkey PRIMARY KEY (avistamento_id);


--
-- Name: dieta dieta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dieta
    ADD CONSTRAINT dieta_pkey PRIMARY KEY (dieta_id);


--
-- Name: estado_conservacao estado_conservacao_nome_estado_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estado_conservacao
    ADD CONSTRAINT estado_conservacao_nome_estado_key UNIQUE (nome_estado);


--
-- Name: estado_conservacao estado_conservacao_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estado_conservacao
    ADD CONSTRAINT estado_conservacao_pkey PRIMARY KEY (estado_id);


--
-- Name: estado estado_nome_estado_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estado
    ADD CONSTRAINT estado_nome_estado_key UNIQUE (nome_estado);


--
-- Name: estado estado_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estado
    ADD CONSTRAINT estado_pkey PRIMARY KEY (estado_id);


--
-- Name: familia familia_nome_familia_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.familia
    ADD CONSTRAINT familia_nome_familia_key UNIQUE (nome_familia);


--
-- Name: familia familia_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.familia
    ADD CONSTRAINT familia_pkey PRIMARY KEY (familia_id);


--
-- Name: funcao funcao_nome_funcao_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcao
    ADD CONSTRAINT funcao_nome_funcao_key UNIQUE (nome_funcao);


--
-- Name: funcao funcao_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcao
    ADD CONSTRAINT funcao_pkey PRIMARY KEY (funcao_id);


--
-- Name: instituicao instituicao_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.instituicao
    ADD CONSTRAINT instituicao_pkey PRIMARY KEY (instituicao_id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (token_id);


--
-- Name: password_reset_tokens password_reset_tokens_token_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_token_key UNIQUE (token);


--
-- Name: utilizador utilizador_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilizador
    ADD CONSTRAINT utilizador_email_key UNIQUE (email);


--
-- Name: utilizador utilizador_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilizador
    ADD CONSTRAINT utilizador_pkey PRIMARY KEY (utilizador_id);


--
-- Name: animal_ameaca animal_ameaca_ameaca_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal_ameaca
    ADD CONSTRAINT animal_ameaca_ameaca_id_fkey FOREIGN KEY (ameaca_id) REFERENCES public.ameaca(ameaca_id) ON DELETE CASCADE;


--
-- Name: animal_ameaca animal_ameaca_animal_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal_ameaca
    ADD CONSTRAINT animal_ameaca_animal_id_fkey FOREIGN KEY (animal_id) REFERENCES public.animal(animal_id) ON DELETE CASCADE;


--
-- Name: animal animal_dieta_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal
    ADD CONSTRAINT animal_dieta_id_fkey FOREIGN KEY (dieta_id) REFERENCES public.dieta(dieta_id);


--
-- Name: animal animal_estado_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal
    ADD CONSTRAINT animal_estado_id_fkey FOREIGN KEY (estado_id) REFERENCES public.estado_conservacao(estado_id);


--
-- Name: animal animal_familia_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.animal
    ADD CONSTRAINT animal_familia_id_fkey FOREIGN KEY (familia_id) REFERENCES public.familia(familia_id);


--
-- Name: avistamento avistamento_animal_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.avistamento
    ADD CONSTRAINT avistamento_animal_id_fkey FOREIGN KEY (animal_id) REFERENCES public.animal(animal_id);


--
-- Name: avistamento avistamento_utilizador_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.avistamento
    ADD CONSTRAINT avistamento_utilizador_id_fkey FOREIGN KEY (utilizador_id) REFERENCES public.utilizador(utilizador_id);


--
-- Name: password_reset_tokens password_reset_tokens_utilizador_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_utilizador_id_fkey FOREIGN KEY (utilizador_id) REFERENCES public.utilizador(utilizador_id) ON DELETE CASCADE;


--
-- Name: utilizador utilizador_estado_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilizador
    ADD CONSTRAINT utilizador_estado_id_fkey FOREIGN KEY (estado_id) REFERENCES public.estado(estado_id);


--
-- Name: utilizador utilizador_funcao_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilizador
    ADD CONSTRAINT utilizador_funcao_id_fkey FOREIGN KEY (funcao_id) REFERENCES public.funcao(funcao_id);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: -
--

GRANT ALL ON SCHEMA public TO cloudsqlsuperuser;


--
-- PostgreSQL database dump complete
--

\unrestrict VTALrBZatkjjCYq5Q2A9pzZA37nyfIRU7YdYFDO0cRHTIdvuWdVMmBWXTg41dQS

