-- Add a Luhn checksum to a number padded to 8 digits.
-- Uses DECODE extension instead of CASE - eg. CASE c2 WHEN '0' THEN 1 WHEN '1' THEN 2...
-- EVEN LENGTH NUMBER
SELECT
    SubscriptionId as 'SubscriptionID',
    Padded + CAST(MOD(9*(d1+d2+d3+d4+d5+d6+d7+d8),10) AS CHAR(1)) AS 'Reference Number'
FROM (
    SELECT
        SubscriptionID,
        Padded,
        CAST(c1 AS INT) as d1,
        DECODE(c2,'0',0,'1',2,'2',4,'3',6,'4',8,'5',1,'6',3,'7',5,'8',7,'9',9) as d2,   
        CAST(c3 AS INT) as d3,
        DECODE(c4,'0',0,'1',2,'2',4,'3',6,'4',8,'5',1,'6',3,'7',5,'8',7,'9',9) as d4, 
        CAST(c5 AS INT) as d5,
        DECODE(c6,'0',0,'1',2,'2',4,'3',6,'4',8,'5',1,'6',3,'7',5,'8',7,'9',9) as d6,
        CAST(c7 AS INT) as d7,
        DECODE(c8,'0',0,'1',2,'2',4,'3',6,'4',8,'5',1,'6',3,'7',5,'8',7,'9',9) as d8
    FROM (
        SELECT 
            SubscriptionID, Padded, SUBSTR(Padded,1,1) as c1, SUBSTR(Padded,2,1) as c2, SUBSTR(Padded,3,1) as c3,
            SUBSTR(Padded,4,1) as c4, SUBSTR(Padded,5,1) as c5, SUBSTR(Padded,6,1) as c6,
            SUBSTR(Padded,7,1) as c7, SUBSTR(Padded,8,1) as c8
        FROM (
            SELECT s.SubscriptionID,
            LPAD(CAST(s.SubscriptionID AS VARCHAR(8)),8,'0') AS Padded
            FROM PUB.Subscription s
        ) AS i
    ) AS c
) AS d

-- ODD LENGTH NUMBER
SELECT
    SubscriptionId as 'SubscriptionID',
    Padded + CAST(MOD(9*(d1+d2+d3+d4+d5+d6+d7),10) AS CHAR(1)) AS 'Reference Number'
FROM (
    SELECT
        SubscriptionID, Padded,
        DECODE(c1,'0',0,'1',2,'2',4,'3',6,'4',8,'5',1,'6',3,'7',5,'8',7,'9',9) as d1,
        CAST(c2 AS INT) as d2,
        DECODE(c3,'0',0,'1',2,'2',4,'3',6,'4',8,'5',1,'6',3,'7',5,'8',7,'9',9) as d3,
        CAST(c4 AS INT) as d4,
        DECODE(c5,'0',0,'1',2,'2',4,'3',6,'4',8,'5',1,'6',3,'7',5,'8',7,'9',9) as d5,
        CAST(c6 AS INT) as d6,
        DECODE(c7,'0',0,'1',2,'2',4,'3',6,'4',8,'5',1,'6',3,'7',5,'8',7,'9',9) as d7
    FROM (
        SELECT 
            SubscriptionID, Padded, SUBSTR(Padded,1,1) as c1, SUBSTR(Padded,2,1) as c2, SUBSTR(Padded,3,1) as c3,
            SUBSTR(Padded,4,1) as c4, SUBSTR(Padded,5,1) as c5, SUBSTR(Padded,6,1) as c6,
            SUBSTR(Padded,7,1) as c7
        FROM (
            SELECT s.SubscriptionID,
            LPAD(CAST(s.SubscriptionID AS VARCHAR(7)),7,'0') AS Padded
            FROM PUB.Subscription s
        ) AS i
    ) AS c
) AS d