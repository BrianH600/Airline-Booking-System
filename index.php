<?php
// 1. CONNECTION: Connects PHP to the MySQL database in XAMPP
$conn = new mysqli("localhost", "root", "", "airline_db");

// 2. PROCESSING: Handles the "Buy Ticket" button
if (isset($_POST['buy_ticket'])) {
    $name = $_POST['name'];
    $f_id = $_POST['flight_id'];

    // TRANSACTION: Requirement 5.0 - Ensures all DB changes happen together or not at all
    $conn->begin_transaction();

    try {
        // SECURITY: Requirement 5.0 - Prepared Statement to prevent SQL Injection
        $stmt = $conn->prepare("INSERT INTO passengers (p_name) VALUES (?)");
        $stmt->bind_param("s", $name); 
        $stmt->execute();
        $p_id = $conn->insert_id; // Gets the ID of the new passenger

        // RECORD MGMT: Links passenger to the flight in the bookings table
        $conn->query("INSERT INTO bookings (passenger_id, flight_id) VALUES ($p_id, $f_id)");

        // CONSTRAINT: Requirement 3.0 - Updates the seat count
        $conn->query("UPDATE flights SET seats_available = seats_available - 1 WHERE id = $f_id");

        $conn->commit(); // Save changes
        echo "<b>Success: Ticket Booked!</b>";
    } catch (Exception $e) {
        $conn->rollback(); // Undo on error
        echo "Error: " . $e->getMessage();
    }
}
?>

<form method="POST" style="margin: 20px;">
    <input type="text" name="name" placeholder="Passenger Name" required>
    <select name="flight_id" required>
        <option value="1">AA344 - New York</option>
        <option value="2">UX234 - London</option>
        <option value="3">BA145 - Paris</option>
        <option value="4">GF233 - Tokyo</option>
    </select>
    <button type="submit" name="buy_ticket">Purchase Ticket</button>
</form>

<table border="1" cellpadding="10">
    <tr><th>Passenger</th><th>Flight #</th><th>Destination</th></tr>
    <?php
    // JOINS: Merges 3 tables (passengers, flights, bookings) into one list
    $sql = "SELECT p.p_name, f.flight_no, f.destination 
            FROM bookings b
            JOIN passengers p ON b.passenger_id = p.id
            JOIN flights f ON b.flight_id = f.id";
    
    $result = $conn->query($sql);
    if($result) {
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".htmlspecialchars($row['p_name'])."</td><td>".$row['flight_no']."</td><td>".$row['destination']."</td></tr>";
        }
    }
    ?>
</table>