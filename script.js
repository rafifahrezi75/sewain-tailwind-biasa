const fs = require('fs');
const path = require('path');

const pages = [
  {
    name: "dashboard",
    file: "index.html",
    icon: "bx-grid-alt",
    title: "Dashboard",
  },
  {
    name: "kategori",
    file: "kategori.html",
    icon: "bx-layer",
    title: "Kategori",
  },
  {
    name: "alat",
    file: "alat.html",
    icon: "bx-wrench",
    title: "Alat Produksi",
  },
  {
    name: "transaksi",
    file: "transaksi.html",
    icon: "bx-shopping-bag",
    title: "Daftar Transaksi",
  },
  {
    name: "pengembalian",
    file: "pengembalian.html",
    icon: "bx-archive-in",
    title: "Pengembalian",
  },
  {
    name: "pelanggan",
    file: "pelanggan.html",
    icon: "bx-group",
    title: "Pelanggan UMKM",
  },
  {
    name: "pengaturan",
    file: "pengaturan.html",
    icon: "bx-cog",
    title: "Pengaturan Sistem",
  },
];

function buildSidebar(activePage) {
  let sidebar = `\n`;
  for (const page of pages) {
    if (page.name === activePage) {
      sidebar += `                <a href="${page.file}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none bg-white text-brand-700 shadow-sm" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx ${page.icon} text-xl shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">${page.title}</span>
                    <div x-show="sidebarOpen" class="ml-auto flex h-2 w-2 shrink-0 rounded-full bg-brand-500"></div>
                </a>\n\n`;
    } else {
      sidebar += `                <a href="${page.file}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx ${page.icon} text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">${page.title}</span>
                </a>\n\n`;
    }
  }
  return sidebar;
}

const basePath = __dirname;

pages.forEach(currentLayoutPage => {
    const filePath = path.join(basePath, currentLayoutPage.file);
    
    // cek apakah file HTML ada
    if (!fs.existsSync(filePath)) {
        console.log(`[Skip] file ${currentLayoutPage.file} tidak ditemukan.`);
        return;
    }
    
    // baca isi html nya
    let htmlContent = fs.readFileSync(filePath, 'utf-8');

    // 1. rombak nav (nyari tag nav)
    const navOpen = '<nav class="flex-1 space-y-2 overflow-y-auto px-3 py-4 scrollbar-hide">';
    const navClose = '</nav>';

    const idxStart = htmlContent.indexOf(navOpen);
    const idxEnd = htmlContent.indexOf(navClose, idxStart);

    if (idxStart !== -1 && idxEnd !== -1) {
        // pisahin header dan part bawahnya nav
        const headerPart = htmlContent.substring(0, idxStart + navOpen.length);
        const footerPart = htmlContent.substring(idxEnd);

        // bikin nav HTML baru
        const newNav = buildSidebar(currentLayoutPage.name);

        // gabungin HTML update-an
        htmlContent = headerPart + newNav + '            ' + footerPart;
    }

    // 2. inject nama halaman ke topbar header
    const topbarTextStart = '<span class="text-lg font-medium text-white hidden sm:block capitalize">';
    const topbarTextEnd = '</span>';
    
    const hStart = htmlContent.indexOf(topbarTextStart);
    if(hStart !== -1) {
        let afterHeader = htmlContent.substring(hStart + topbarTextStart.length);
        const hEnd = afterHeader.indexOf(topbarTextEnd);
        
        if(hEnd !== -1) {
            const part1 = htmlContent.substring(0, hStart + topbarTextStart.length);
            const part2 = afterHeader.substring(hEnd);
            
            // update header teks
            htmlContent = part1 + '\n                        ' + currentLayoutPage.title + '\n                    ' + part2;
        }
    }
    
    // 3. update tag title seo nya
    htmlContent = htmlContent.replace(/<title>.*?<\/title>/, `<title>${currentLayoutPage.title} | Admin Dashboard</title>`);

    // save file HTML nya
    fs.writeFileSync(filePath, htmlContent);
    console.log(`[Berhasil] Sidebar dan Judul di-update pada halaman -> ${currentLayoutPage.file}`);
});