const label = {
    BANK: "Bank",
    KOPERASI: "Koperasi",
    KANTOR: "Rumah Sakit",
    OPNAME: "Rawat Inap",
    OBAT: "Farmasi",
    LABORAT: "Laborat",
    RADIOLOGI: "Radiologi",
    POLI: "Rawat Jalan",
    BKIA: "BKIA",
    SERAGAM: "Seragam",
    "CHEQ UP": "Check Up",
    ORGANISASI: "Organisasi",
    LAIN2: "Lain-lain",
    "JUMLAH POTONGAN": "Total",
    "JUMLAH DITERIMA": "Penyerahan Gaji",
};
const position = {
    BANK: {
        sheet: 1,
        column: "L",
    },
    KOPERASI: {
        sheet: 1,
        column: "K",
    },
    KANTOR: {
        sheet: 1,
        column: "M",
    },
    OPNAME: {
        sheet: 4,
        column: "E",
    },
    OBAT: {
        sheet: 4,
        column: "F",
    },
    LABORAT: {
        sheet: 4,
        column: "G",
    },
    RADIOLOGI: {
        sheet: 4,
        column: "H",
    },
    POLI: {
        sheet: 4,
        column: "I",
    },
    BKIA: {
        sheet: 4,
        column: "J",
    },
    SERAGAM: {
        sheet: 4,
        column: "K",
    },
    "CHEQ UP": {
        sheet: 4,
        column: "L",
    },
    ORGANISASI: {
        sheet: 1,
        column: "N",
    },
    LAIN2: {
        sheet: 1,
        column: "O",
    },
    "JUMLAH POTONGAN": {
        sheet: 1,
        column: "P",
    },
    "JUMLAH DITERIMA": {
        sheet: 1,
        column: "Q",
    },
};

console.log(JSON.stringify(position));
