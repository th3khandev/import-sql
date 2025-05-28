# 🚀 SQL Database Importer - Modern Web Application

<div align="center">

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)

*A beautiful, modern, and efficient solution for importing large SQL databases into MySQL*

[🎯 Features](#-features) • [🚀 Quick Start](#-quick-start) • [📖 Usage](#-usage) • [🛠️ Configuration](#️-configuration) • [🤝 Contributing](#-contributing)

</div>

---

## 🌟 Overview

**SQL Database Importer** is a modern, web-based application designed to solve the common problem of importing large SQL database files into MySQL. Built with a beautiful Tailwind CSS interface and optimized PHP backend, it provides a seamless experience for database administrators and developers.

### 🎯 Why This Tool?

- **🚫 No more phpMyAdmin timeouts** - Handle files larger than 1GB
- **📊 Real-time progress tracking** - See exactly what's happening
- **🎨 Modern UI/UX** - Beautiful, responsive interface
- **⚡ Memory optimized** - Efficient processing for large files
- **🔒 Secure** - Proper validation and error handling

---

## ✨ Features

### 🎨 **Modern Interface**
- **Responsive Design** - Works perfectly on desktop, tablet, and mobile
- **Tailwind CSS** - Beautiful, modern styling with gradients and animations
- **Font Awesome Icons** - Intuitive visual elements
- **Dark/Light Theme** - Easy on the eyes

### 🚀 **Performance & Efficiency**
- **Memory Optimization** - Handles files up to 2GB
- **Chunk Processing** - Reads files in small pieces to prevent memory overflow
- **Progress Tracking** - Real-time progress bar with detailed information
- **Auto-scroll Messages** - Keep track of all operations

### 🔧 **Advanced Features**
- **Dual Input Methods** - Upload files or specify server paths
- **File Validation** - Ensures only .sql files are processed
- **Error Handling** - Comprehensive error messages and recovery options
- **Operation Logging** - Detailed logs of all database operations
- **Auto-cleanup** - Removes temporary files automatically

### 🛡️ **Security & Reliability**
- **Input Validation** - Prevents malicious file uploads
- **Connection Testing** - Validates database credentials before processing
- **Transaction Safety** - Proper error handling for database operations
- **Memory Management** - Garbage collection and memory optimization

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL/MariaDB server
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/sql-database-importer.git
   cd sql-database-importer
   ```

2. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

3. **Open your browser**
   ```
   http://localhost:8000/sql-importer.php
   ```

That's it! 🎉 You're ready to import your databases.

---

## 📖 Usage

### 🔌 **Database Connection**

Fill in your database connection details:

| Field | Description | Example |
|-------|-------------|---------|
| **Host** | Database server address | `localhost` or `127.0.0.1` |
| **Database Name** | Target database name | `my_website_db` |
| **Username** | Database username | `root` or `db_user` |
| **Password** | Database password | Your secure password |

### 📁 **File Selection**

Choose one of two methods:

#### Method 1: Upload File
- Click "Choose File" and select your `.sql` file
- Maximum file size: **1GB**
- Supported format: `.sql` files only

#### Method 2: Server Path
- If your SQL file is already on the server
- Enter the filename without the `.sql` extension
- Example: For `backup.sql`, enter `backup`

### ▶️ **Import Process**

1. Click **"Start Database Import"**
2. Watch the real-time progress bar
3. Monitor detailed operation logs
4. Get notified when import completes

---

## 🛠️ Configuration

### PHP Configuration

The application automatically configures PHP settings for optimal performance:

```php
ini_set('memory_limit', '2048M');        // 2GB memory limit
ini_set('max_execution_time', 0);        // No time limit
ini_set('upload_max_filesize', '1024M'); // 1GB upload limit
ini_set('post_max_size', '1024M');       // 1GB POST limit
```

### Server Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| **PHP Version** | 7.4 | 8.0+ |
| **Memory** | 512MB | 2GB+ |
| **MySQL** | 5.7 | 8.0+ |
| **Disk Space** | 2x file size | 5x file size |

---

## 🎯 Advanced Features

### 📊 **Progress Tracking**

- **Real-time progress bar** - Visual progress indicator
- **Line-by-line processing** - See exactly which line is being processed
- **Operation categorization** - Different colors for different SQL operations:
  - 🟢 **Green**: CREATE TABLE, CREATE INDEX (Success operations)
  - 🔵 **Blue**: INSERT INTO, SELECT (Info operations)
  - 🟡 **Yellow**: ALTER, UPDATE, TRUNCATE (Warning operations)
  - 🔴 **Red**: DROP TABLE, DELETE (Destructive operations)

### 🔄 **Memory Management**

- **Chunk processing** - Reads files in 1000-line chunks
- **Garbage collection** - Automatic memory cleanup every 100 operations
- **Stream processing** - Uses file handles instead of loading entire file

### 🛡️ **Error Handling**

- **Connection validation** - Tests database connection before processing
- **File validation** - Ensures file exists and is readable
- **SQL error reporting** - Detailed error messages for failed queries
- **Graceful recovery** - Clean exit on errors with helpful messages

---

## 🎨 Screenshots

### Main Interface
![Main Interface](https://via.placeholder.com/800x600/3B82F6/FFFFFF?text=Modern+SQL+Importer+Interface)

### Processing View
![Processing View](https://via.placeholder.com/800x600/10B981/FFFFFF?text=Real-time+Progress+Tracking)

---

## 🔧 Troubleshooting

### Common Issues

#### ❌ **Memory Limit Exceeded**
```
Solution: Increase PHP memory_limit in php.ini or use smaller files
```

#### ❌ **Connection Failed**
```
Solution: Check database credentials and ensure MySQL is running
```

#### ❌ **File Upload Failed**
```
Solution: Check file permissions and upload_max_filesize setting
```

#### ❌ **Timeout Error**
```
Solution: Increase max_execution_time or use command line import
```

### Performance Tips

1. **Use server path method** for very large files (>500MB)
2. **Ensure sufficient disk space** (at least 2x file size)
3. **Close other applications** to free up memory
4. **Use SSD storage** for better I/O performance

---

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### 🐛 **Bug Reports**
- Use the [issue tracker](https://github.com/yourusername/sql-database-importer/issues)
- Include PHP version, file size, and error messages
- Provide steps to reproduce the issue

### 💡 **Feature Requests**
- Suggest new features via [GitHub issues](https://github.com/yourusername/sql-database-importer/issues)
- Explain the use case and expected behavior

### 🔧 **Pull Requests**
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- **Tailwind CSS** - For the beautiful styling framework
- **Font Awesome** - For the amazing icons
- **PHP Community** - For the robust language and ecosystem
- **MySQL Team** - For the reliable database system

---

## 📞 Support

- 📧 **Email**: support@yourdomain.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/yourusername/sql-database-importer/issues)
- 📖 **Documentation**: [Wiki](https://github.com/yourusername/sql-database-importer/wiki)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/yourusername/sql-database-importer/discussions)

---

<div align="center">

**Made with ❤️ by developers, for developers**

⭐ **Star this repo if it helped you!** ⭐

</div>

---
