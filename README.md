# iPaymu Availability Plugin for Moodle

Welcome to the iPaymu Availability Plugin for Moodle!

This plugin allows you to integrate pay-per-access features for specific sections, topics, or resources (like quizzes and assignments) within your Moodle courses. It gives educators full control over what content students can access, while providing students with flexible payment options.

## Features

- Pay-per-access for course sections or topics
- Payment integration for individual resources (quizzes, assignments, etc.)
- Seamless setup with the iPaymu payment system
- Complete control over content availability based on payment

## Requirements

1. **Install the Enrol iPaymu Plugin First**  
   Before using the iPaymu Availability plugin, you must first install the Enrol iPaymu plugin.

2. **Download and Set Up the Enrol iPaymu Plugin**  
   You can download and configure the Enrol iPaymu plugin from the following link:  
   [Enrol iPaymu Plugin Repository](https://github.com/ipaymu/moodle-enrol_ipaymu)

### Installation

1. First, you need to login as admin to your moodle site.
2. Then, go to **Site administration** -> **Plugins** -> **Install plugins**
3. You'll see the choose file button or you can drag and drop the plugin zip file to the box. Choose or drop the zip file plugin.
4. Then, click **install plugin from ZIP file**.
5. Then, click **continue** after installation complete

## Usage

1. After installation, navigate to a course or section where you want to enable pay-per-access.
2. Set up the iPaymu Availability conditions for the desired sections, topics, or resources.
3. Customize your payment settings and define what content students can access after payment.

## Configure Payment Per Section

1. **Navigate to Your Course**  
   Go to **Site administration** → **Courses** → **Manage courses and categories**, select **your course**, and click **Edit**.

2. **Enter Edit Mode**  
   Inside the course, switch to **Edit mode**, then go to the **Course** tab.

3. **Select Section or Topic**  
   Choose the **Section** or **Topic** you want to configure, and click **Edit Topic**.

4. **Restrict Access**  
   Scroll down to the **Restrict access** section and click **Add restriction...**.

5. **Select iPaymu Payment**  
   From the list of restrictions, choose **iPaymu payment**.

6. **Configure Payment Details**  
   Fill in the following fields with your desired values:

   - **Currency**: Select the currency for payment.
   - **Cost**: Enter the amount you want to charge.
   - **Item Name**: Provide a name for the item or section.

7. **Save Changes**  
   After configuring all the details, make sure to click **Save changes** to apply the settings.

## Support

If you need assistance or have any issues with the plugin, feel free to reach out through the plugin's GitHub repository or contact the support team.

## License

This plugin is open-source and licensed under the [GPL License](https://www.gnu.org/licenses/gpl-3.0.html).
