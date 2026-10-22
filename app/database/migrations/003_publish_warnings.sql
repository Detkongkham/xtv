-- Publisher warnings that do not stop a publish, e.g. Chinese characters missing from the font subset (P2-C4)
ALTER TABLE publish_log ADD COLUMN warnings TEXT NULL AFTER error;
