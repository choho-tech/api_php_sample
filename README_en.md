# ChohoCloud PHP Sample

Please note: This code is provided solely as a programming reference under PHP.

**Polling should not be used in the production environment** to determine the completion of tasks as shown in the sample code. Instead, **callbacks should be used** to receive task completion information (i.e., configure the `notification` field in the task startup information).

To get started quickly or view more algorithm invocation examples, we suggest starting with our Python sample to understand HTTP request methods and request parameters: [https://gitee.com/chohotech/api_python_sample](https://gitee.com/chohotech/api_python_sample) (Github: [https://github.com/choho-tech/api_python_sample](https://github.com/choho-tech/api_python_sample))

## Usage Steps

This example depends on `cURL`. In `Ubuntu`, you can install the runtime environment using the following command:

```bash
sudo apt install php-cli php-curl
```

In the constants section of `seg.php` (lines 3-9), fill in the corresponding information and then run the command line.

```bash
php seg.php
```

After running, segmented results `processed_mesh.stl` and `seg_labels.txt` will be generated in the same directory as `seg.php`.

## Example

- This example demonstrates:
  1. How to create a new task JSON
  2. How to create a new task on the server
  3. How to query task status from the server and wait for task completion
  4. How to retrieve task results
  5. How to parse task results
- Please note that while we demonstrate how to perform a segmentation task here, other tasks follow similar patterns, and users can easily adapt them with simple modifications.
- This example demonstrates how to segment an STL jaw file and write the results to disk.

## Code License

This repository is open source under the AGPL v3.0 license. If you use code from this repository in your project, you must provide the source code to users (including SaaS users). If you are a paying customer of Chohotech, this code is licensed to you according to our subscription agreement, and you are not obligated to comply with the AGPL v3.0 open-source license.
