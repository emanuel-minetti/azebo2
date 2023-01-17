CREATE TABLE working_day_part
(
    id             INT8 AUTO_INCREMENT,
    working_day_id INT(11) NOT NULL,
    begin          TIME NULL,
    end            TIME NULL,
    mobile_working TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT working_day_part_pk
        PRIMARY KEY (id),
    CONSTRAINT working_day_part_working_day_id_fk
        FOREIGN KEY (working_day_id) REFERENCES working_day (id));

INSERT INTO working_day_part (working_day_id, begin, end, mobile_working)
    SELECT id, begin, end, mobile_working FROM working_day WHERE NOT (begin IS NULL AND end IS NULL);

ALTER TABLE working_day
    DROP COLUMN begin;

ALTER TABLE working_day
    DROP COLUMN end;

ALTER TABLE working_day
    DROP COLUMN break;

ALTER TABLE working_day
    DROP COLUMN mobile_working;

ALTER TABLE working_day
    DROP COLUMN afternoon;

ALTER TABLE working_day
    DROP COLUMN afternoon_begin;

ALTER TABLE working_day
    DROP COLUMN afternoon_end;
