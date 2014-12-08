-- Convert "Sunday", "Monday", etc to 1,2,...
SELECT DAYOFWEEK(STR_TO_DATE(CONCAT(SA.answerText,' 2 2014'), '%W %U %Y'))
-- Convert "Sunday", "Monday", etc and "Sun", "Mon", etc to 1,2,...
SELECT 
    CASE 'Monday'
        WHEN 'SUN' THEN 1 WHEN 'Sunday' THEN 1    
        WHEN 'MON' THEN 2 WHEN 'Monday' THEN 2    
        WHEN 'TUE' THEN 3 WHEN 'Tuesday' THEN 3   
        WHEN 'WED' THEN 4 WHEN 'Wednesday' THEN 4 
        WHEN 'THU' THEN 5 WHEN 'Thursday' THEN 5	
        WHEN 'FRI' THEN 6 WHEN 'Friday' THEN 6	
        WHEN 'SAT' THEN 7 WHEN 'Saturday' THEN 7	
        ELSE 0
    END
