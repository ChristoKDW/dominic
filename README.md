# EduCourse - Platform Digital Business Technology

## 🎯 Deskripsi Proyek

**EduCourse** adalah platform web native yang menggabungkan pendidikan digital, marketplace, manajemen keuangan, dan manajemen bisnis dalam satu ekosistem terintegrasi. Platform ini dirancang khusus untuk kompetisi "Digital Business Technology (Web & Apps)" dengan fokus pada pengembangan skill dan bisnis digital.

### 🏆 Ruang Lingkup Lomba
- **Jenjang**: SMP - SMA/K Sederajat
- **Format**: Individu
- **Fokus**: Kreativitas ide, penerapan teknologi, dan dampak untuk dunia usaha/masyarakat

## ✨ Fitur Utama

### 🎓 1. Platform Edukasi Bisnis
- **Kursus Online**: Berbagai kursus tentang teknologi bisnis digital
- **Sistem Enrollment**: Pendaftaran dan tracking progress kursus
- **Multi-level Content**: Beginner, Intermediate, Advanced
- **Instructor Dashboard**: Panel untuk pengajar/instruktur

### 🛒 2. Marketplace Mini
- **Jual Beli Produk Digital**: Template, tools, software, e-books
- **Sistem Kategori**: Pengelompokan produk yang terorganisir
- **Management Inventory**: Tracking stok dan harga
- **Search & Filter**: Pencarian produk yang powerful

### 💰 3. Aplikasi Keuangan Sederhana
- **Tracking Transaksi**: Pemasukan dan pengeluaran
- **Kategorisasi**: Organisasi transaksi berdasarkan kategori
- **Laporan Keuangan**: Grafik dan analisis finansial
- **Dashboard Analytics**: Visualisasi data keuangan

### 🏢 4. Sistem Manajemen Usaha
- **Registrasi Bisnis**: Pendaftaran profil bisnis
- **Business Portfolio**: Showcase bisnis digital
- **Contact Management**: Informasi kontak dan komunikasi
- **Status Monitoring**: Tracking status operasional bisnis

## 🛠️ Teknologi yang Digunakan

### Backend
- **PHP 7.4+**: Server-side scripting
- **MySQL**: Database management system
- **PDO**: Database abstraction layer
- **Session Management**: User authentication & authorization

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Modern styling dengan custom properties
- **Bootstrap 5**: Responsive framework
- **JavaScript ES6+**: Interactive functionality
- **Chart.js**: Data visualization
- **Font Awesome**: Icon library

### Database
- **MySQL**: Relational database
- **Structured Schema**: Normalized tables with foreign keys
- **Data Integrity**: Constraints and validations

## 📁 Struktur Proyek

```
Educourse/
├── assets/
│   ├── css/
│   │   └── style.css          # Custom styling
│   ├── js/
│   │   └── script.js          # JavaScript functionality
│   └── images/                # Media assets
├── config/
│   └── database.php           # Database configuration
├── includes/
│   ├── header.php             # Global header template
│   └── footer.php             # Global footer template
├── modules/                   # Feature modules (future expansion)
├── index.php                  # Homepage
├── login.php                  # User authentication
├── register.php               # User registration
├── dashboard.php              # User dashboard
├── courses.php                # Course listing
├── marketplace.php            # Product marketplace
├── finance.php                # Financial management
├── business.php               # Business management
├── logout.php                 # Session termination
├── database.sql               # Database schema
└── README.md                  # Project documentation
```

## 📊 Database Schema

### Tabel Utama
1. **users** - Data pengguna dan autentikasi
2. **categories** - Kategori untuk produk dan kursus
3. **courses** - Data kursus dan pembelajaran
4. **lessons** - Materi pembelajaran per kursus
5. **enrollments** - Pendaftaran pengguna ke kursus
6. **products** - Produk digital di marketplace
7. **orders** - Pesanan dan transaksi marketplace
8. **transactions** - Data keuangan pengguna
9. **businesses** - Profil bisnis pengguna

### Relasi Database
- One-to-Many: User → Courses, Products, Transactions, Businesses
- Many-to-Many: Users ↔ Courses (melalui enrollments)
- Foreign Keys: Memastikan integritas data

## 🚀 Instalasi dan Setup

### Prasyarat
- **XAMPP/WAMP/LAMP**: Web server dengan PHP dan MySQL
- **PHP 7.4+**: Server-side scripting
- **MySQL 5.7+**: Database server
- **Modern Browser**: Chrome, Firefox, Safari, Edge

### Langkah Instalasi

1. **Clone/Download Proyek**
   ```bash
   # Jika menggunakan Git
   git clone [repository-url]
   
   # Atau download dan extract ke folder web server
   # Contoh: C:\wamp64\www\Educourse\
   ```

2. **Setup Database**
   ```sql
   -- Buka phpMyAdmin atau MySQL client
   -- Import file database.sql
   -- Atau jalankan script secara manual
   ```

3. **Konfigurasi Database**
   ```php
   // Edit config/database.php
   private $host = "localhost";
   private $db_name = "educourse_db";
   private $username = "root";
   private $password = "";
   ```

4. **Akses Aplikasi**
   ```
   http://localhost/Educourse/
   ```

### Default Login
- **Admin**: username `admin`, password `admin123`
- **User Baru**: Registrasi melalui halaman pendaftaran

## 💡 Panduan Penggunaan

### Untuk Siswa/Peserta
1. **Registrasi Akun**: Buat akun baru melalui halaman pendaftaran
2. **Eksplorasi Kursus**: Browse dan ikuti kursus yang tersedia
3. **Jual Beli Produk**: Gunakan marketplace untuk transaksi
4. **Kelola Keuangan**: Catat dan pantau keuangan pribadi/bisnis
5. **Daftarkan Bisnis**: Promosikan bisnis digital Anda

### Untuk Instruktur
1. **Daftar sebagai Instruktur**: Ajukan aplikasi instruktur
2. **Buat Kursus**: Develop dan upload materi pembelajaran
3. **Manage Siswa**: Pantau progress dan engagement
4. **Analisis Performance**: Lihat statistik kursus

### Untuk Entrepreneur
1. **Business Registration**: Daftarkan profil bisnis
2. **Financial Tracking**: Monitor cash flow dan profitability
3. **Product Selling**: Jual produk/jasa di marketplace
4. **Skill Development**: Tingkatkan kemampuan melalui kursus

## 🎨 Fitur Teknis

### Responsiveness
- **Mobile-First Design**: Optimized untuk semua device
- **Bootstrap Grid**: Flexible layout system
- **Touch-Friendly**: Interaksi yang mudah di mobile

### Security
- **Password Hashing**: bcrypt untuk keamanan password
- **SQL Injection Prevention**: Prepared statements
- **Session Security**: Proper session management
- **Input Validation**: Client dan server-side validation

### Performance
- **Optimized Queries**: Efficient database operations
- **Image Optimization**: Compressed assets
- **Caching Strategy**: Browser dan server caching
- **Minimal Dependencies**: Fast loading time

### User Experience
- **Intuitive Navigation**: Clear menu structure
- **Visual Feedback**: Loading states dan notifications
- **Error Handling**: Friendly error messages
- **Accessibility**: WCAG guidelines compliance

## 📈 Fitur Analitik

### Dashboard Metrics
- **Course Enrollment**: Tracking pendaftaran kursus
- **Sales Analytics**: Analisis penjualan produk
- **Financial Reports**: Laporan keuangan komprehensif
- **Business Growth**: Metrik pertumbuhan bisnis

### Visualisasi Data
- **Charts & Graphs**: Chart.js untuk visualisasi
- **Progress Tracking**: Progress bar dan indicators
- **Trend Analysis**: Analisis tren dan pattern
- **Export Functionality**: Export data ke CSV/PDF

## 🔮 Roadmap Pengembangan

### Phase 1 (Current)
- ✅ Core platform functionality
- ✅ User management system
- ✅ Basic course and marketplace features
- ✅ Financial tracking

### Phase 2 (Future)
- 🔄 Advanced course features (video, quiz)
- 🔄 Payment gateway integration
- 🔄 Advanced analytics dashboard
- 🔄 Mobile app development

### Phase 3 (Advanced)
- 📋 AI-powered recommendations
- 📋 Social learning features
- 📋 Multi-language support
- 📋 API development

## 🤝 Kontribusi

### How to Contribute
1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

### Code Style
- **PHP**: PSR-12 coding standard
- **JavaScript**: ES6+ with consistent formatting
- **CSS**: BEM methodology for naming
- **Comments**: Comprehensive code documentation

## 📄 Lisensi

Proyek ini dibuat untuk tujuan edukasi dan kompetisi. Silakan gunakan dan modifikasi sesuai kebutuhan pembelajaran.

## 📞 Kontak & Support

- **Email**: info@educourse.com
- **Website**: [Demo Link]
- **Documentation**: README.md dan komentar kode
- **Issues**: GitHub Issues (jika ada repository)

## 🙏 Acknowledgments

- **Bootstrap Team**: Untuk framework CSS yang amazing
- **Chart.js**: Untuk library visualisasi data
- **Font Awesome**: Untuk icon library
- **PHP Community**: Untuk dokumentasi dan resources
- **Open Source Community**: Untuk inspirasi dan tools

---

**EduCourse Platform** - Empowering Digital Business Education
*Dibuat dengan ❤️ untuk masa depan pendidikan digital Indonesia*