    <style>
        .hover-text {
            cursor: pointer;
            color: rgb(6, 6, 182);
            position: relative;
        }

        /* Tooltip styling */
        .custom-tooltip {
            display: none;
            position: absolute;
            background-color: #fff;
            /* White background */
            color: #333;
            /* Dark text for contrast */
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            /* Larger text size */
            /* font-weight: bold; */
            /* Optional: Make text bold */
            max-width: 250px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
            /* Optional: Add a light gray border for better visibility */
            z-index: 1000;
        }

        .custom-tooltip::after {
            content: '';
            position: absolute;
            top: 50%;
            left: -5px;
            transform: translateY(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: transparent #fff transparent transparent;
            /* Match tooltip background */
            box-shadow: -1px 1px 3px rgba(0, 0, 0, 0.1);
            /* Optional: Add a subtle shadow to the pointer */
        }

        /* Styling for (OUT) text */
        .hover-text:contains("(OUT)") {
            color: red;
        }

        /* Styling for (IN) text */
        .hover-text:contains("(IN)") {
            color: rgb(6, 109, 6);
        }
    </style>
