-- reduces the error due to rounding off
ALTER TABLE `0_attendance_metrics` MODIFY `amount` DECIMAL(8,4) DEFAULT 0;