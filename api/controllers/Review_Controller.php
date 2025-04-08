<?php
require_once __DIR__ . '/../objects/Review.php';

class Review_Controller {
    private $conn;
    private $table_name = "reviews";
    private $review;

    public function __construct($db) {
        $this->conn = $db;
        $this->review = new Review($db);
    }

    // Create review
    public function create() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));

        // Validate required fields
        if(empty($data->customerName) || empty($data->rating) || empty($data->title) || empty($data->content)) {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create review. Data is incomplete."));
            return;
        }

        // Set review property values
        $this->review->customerName = $data->customerName;
        $this->review->rating = $data->rating;
        $this->review->title = $data->title;
        $this->review->content = $data->content;

        // Create the review
        if($this->review->create()) {
            http_response_code(201);
            echo json_encode(array(
                "message" => "Review was created.",
                "id" => $this->review->id
            ));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create review."));
        }
    }

    // Read all reviews
    public function read() {
        $reviews = $this->review->read();
        
        if($reviews) {
            http_response_code(200);
            echo json_encode($reviews);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No reviews found."));
        }
    }

    // Read single review
    public function readOne($id) {
        // Set review ID
        $this->review->id = $id;

        // Get review
        $review = $this->review->readOne();
        
        if($review) {
            http_response_code(200);
            echo json_encode($review);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Review does not exist."));
        }
    }

    // Update review
    public function update($id) {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));

        // Validate required fields
        if(empty($data->customerName) || empty($data->rating) || empty($data->title) || empty($data->content)) {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update review. Data is incomplete."));
            return;
        }

        // Set review property values
        $this->review->id = $id;
        $this->review->customerName = $data->customerName;
        $this->review->rating = $data->rating;
        $this->review->title = $data->title;
        $this->review->content = $data->content;

        // Update the review
        if($this->review->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Review was updated."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update review."));
        }
    }

    // Delete review
    public function delete($id) {
        // Set review ID
        $this->review->id = $id;

        // Delete the review
        if($this->review->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Review was deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete review."));
        }
    }

    // Get average rating
    public function getAverageRating() {
        $rating = $this->review->getAverageRating();
        
        if($rating) {
            http_response_code(200);
            echo json_encode(array(
                "average_rating" => $rating->average_rating,
                "total_reviews" => $rating->total_reviews
            ));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No ratings found."));
        }
    }
}
?> 