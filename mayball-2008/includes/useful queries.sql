SELECT SUM(TotalCost) as "Total Price"
FROM applications;

SELECT TicketType AS "Ticket Type", COUNT(*) AS "Total Sold" FROM tickets GROUP BY TicketType

SELECT * from applications where Confirmed <> 0;

SELECT tickets.TicketType, COUNT(TicketType) FROM applications, tickets AS '' WHERE tickets.ApplicationID = applications.ApplicationID { AND applications.Confirmed <> 0} GROUP BY TicketType;