.dash-prv {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}
.dash-prv-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}
@media (max-width: 767.98px) {
    .dash-prv-meta { grid-template-columns: 1fr; }
}
.dash-prv-meta-card {
    background: #fff;
    border: 1px solid #d7e2f0;
    border-radius: 0.65rem;
    padding: 0.75rem 0.9rem;
    box-shadow: 0 1px 2px rgba(15, 40, 80, 0.04);
}
.dash-prv-meta-label {
    font-size: 0.68rem;
    font-weight: 700;
    color: #5a7299;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.2rem;
}
.dash-prv-meta-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: #12233f;
    word-break: break-word;
    line-height: 1.35;
}
.dash-prv-section {
    background: #fff;
    border: 1px solid #dce5f1;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(15, 40, 80, 0.04);
}
.dash-prv-section-hd {
    background: linear-gradient(180deg, #f4f8fd 0%, #eaf1fa 100%);
    border-bottom: 1px solid #dde5f3;
    padding: 0.7rem 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
}
.dash-prv-section-num {
    width: 1.7rem;
    height: 1.7rem;
    border-radius: 50%;
    background: #035ab3;
    color: #fff;
    font-size: 0.74rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 0.05rem;
}
.dash-prv-section-num--sub {
    width: auto;
    min-width: 2rem;
    padding: 0 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.7rem;
}
.dash-prv-section-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #12233f;
    line-height: 1.35;
}
.dash-prv-section-tamil {
    font-size: 0.76rem;
    color: #5a7299;
    margin-top: 0.15rem;
    line-height: 1.4;
}
.dash-prv-section-body { padding: 1rem 1.05rem 1.1rem; }
.dash-prv-question + .dash-prv-question {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px dashed #d5deed;
}
.dash-prv-question .dash-prv-section-hd {
    margin-bottom: 0.65rem;
    border-radius: 0.5rem;
    border: 1px solid #e3eaf4;
}
.dash-prv-question .dash-prv-section-body { padding: 0; }
.dash-prv-personal {
    display: grid;
    grid-template-columns: 10.5rem minmax(0, 1fr);
    gap: 1.15rem;
    align-items: start;
}
@media (max-width: 767.98px) {
    .dash-prv-personal { grid-template-columns: 1fr; }
}
.dash-prv-media {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 0.75rem;
    background: #f7fafd;
    border: 1px solid #e3e8f0;
    border-radius: 0.65rem;
}
.dash-prv-media-label {
    font-size: 0.66rem;
    font-weight: 700;
    color: #5a7299;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.35rem;
    text-align: center;
}
.dash-prv-thumb img,
.dash-prv-no-img {
    display: block;
    margin: 0 auto;
    border: 1px solid #d5e0ee;
    border-radius: 0.5rem;
    background: #fff;
}
.dash-prv-thumb--photo img,
.dash-prv-no-img--photo {
    width: 7.25rem;
    height: 8.75rem;
    object-fit: cover;
}
.dash-prv-thumb--sign img,
.dash-prv-no-img--sign {
    width: 100%;
    max-width: 8.5rem;
    height: 3.5rem;
    object-fit: contain;
    background: #fff;
    padding: 0.25rem;
}
.dash-prv-no-img {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9aa8bf;
    font-size: 0.7rem;
    text-align: center;
    border-style: dashed;
    background: #f0f4f9;
}
.dash-prv-details {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem 0.9rem;
    min-width: 0;
}
@media (max-width: 991.98px) {
    .dash-prv-details { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .dash-prv-details { grid-template-columns: 1fr; }
}
.dash-prv-field--full { grid-column: 1 / -1; }
.dash-prv-label {
    font-size: 0.68rem;
    font-weight: 700;
    color: #5a7299;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.25rem;
}
.dash-prv-value {
    font-size: 0.92rem;
    color: #12233f;
    font-weight: 600;
    padding: 0.55rem 0.7rem;
    background: #f7fafd;
    border: 1px solid #e3e8f0;
    border-radius: 0.45rem;
    min-height: 2.35rem;
    word-break: break-word;
    line-height: 1.4;
}
.dash-prv-value.is-empty {
    color: #9aa8bf;
    font-style: italic;
    font-weight: 400;
}
.dash-prv-subhead {
    font-size: 0.82rem;
    font-weight: 700;
    color: #1a3a72;
    margin: 0.35rem 0 0.55rem;
    padding-bottom: 0.3rem;
    border-bottom: 1px dashed #dde5f3;
}
.dash-prv-table-wrap {
    overflow-x: auto;
    border: 1px solid #e3e8f0;
    border-radius: 0.55rem;
    margin-bottom: 0.9rem;
}
.dash-prv-table {
    width: 100%;
    font-size: 0.8rem;
    border-collapse: collapse;
    margin: 0;
    min-width: 36rem;
}
.dash-prv-table th {
    background: #eef3fb;
    color: #1a2a4a;
    font-weight: 700;
    padding: 0.5rem 0.55rem;
    border: 1px solid #dde5f3;
    font-size: 0.72rem;
    white-space: nowrap;
    text-align: center;
}
.dash-prv-table td {
    padding: 0.5rem 0.55rem;
    border: 1px solid #e8edf6;
    vertical-align: middle;
    color: #2c3e5e;
    text-align: center;
}
.dash-prv-table td.dash-prv-td-left { text-align: left; }
.dash-prv-table tr:nth-child(even) td { background: #f8fafd; }
.dash-prv-doc-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: #e8f2ff;
    color: #035ab3;
    border-radius: 999px;
    padding: 0.25rem 0.7rem;
    font-size: 0.74rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}
.dash-prv-doc-pill .fa-file-pdf-o {
    color: #d9534f;
}
.dash-prv-doc-pill:hover { background: #d6e8ff; color: #024a98; text-decoration: none; }
.dash-prv .fa-file-pdf-o {
    color: #d9534f;
}
.dash-prv-doc-empty { color: #9aa8bf; font-size: 0.78rem; }
.dash-prv-yes {
    background: #d4edda;
    color: #155724;
    border-radius: 0.3rem;
    padding: 0.2rem 0.65rem;
    font-size: 0.76rem;
    font-weight: 700;
}
.dash-prv-no {
    background: #f8d7da;
    color: #721c24;
    border-radius: 0.3rem;
    padding: 0.2rem 0.65rem;
    font-size: 0.76rem;
    font-weight: 700;
}
.dash-prv-grid-2,
.dash-prv-grid-4,
.dash-prv-grid-id {
    display: grid;
    gap: 0.75rem;
}
.dash-prv-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.dash-prv-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.dash-prv-grid-id { grid-template-columns: repeat(2, minmax(0, 1fr)); }
@media (max-width: 991.98px) {
    .dash-prv-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .dash-prv-grid-2,
    .dash-prv-grid-4,
    .dash-prv-grid-id { grid-template-columns: 1fr; }
}
.dash-prv .wx-summary-table-wrap {
    overflow-x: auto;
    border: 1px solid #e3e8f0;
    border-radius: 0.55rem;
}
.dash-prv .wx-summary-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.78rem;
    margin: 0;
}
.dash-prv .wx-summary-table thead th {
    background: #eef3fb;
    color: #1a2a4a;
    font-weight: 700;
    padding: 0.5rem 0.5rem;
    border: 1px solid #dde5f3;
    text-align: center;
}
.dash-prv .wx-summary-table td {
    padding: 0.5rem;
    border: 1px solid #e8edf6;
    vertical-align: top;
    color: #2c3e5e;
}
.dash-prv .wx-sum-main { display: block; font-weight: 700; }
.dash-prv .wx-sum-sub { display: block; font-size: 0.72rem; color: #5a7299; margin-top: 0.15rem; }
.dash-prv .wx-period-dates { display: flex; gap: 0.65rem; flex-wrap: wrap; }
.dash-prv .wx-period-mini { display: flex; flex-direction: column; }
.dash-prv .wx-period-label { font-size: 0.62rem; color: #5a7299; font-weight: 700; }
.dash-prv .wx-period-duration { display: flex; gap: 0.4rem; margin-top: 0.4rem; }
.dash-prv .wx-period-dur-cell {
    background: #f0f6ff;
    border: 1px solid #d5e4f7;
    border-radius: 0.3rem;
    padding: 0.2rem 0.4rem;
    text-align: center;
    min-width: 3.1rem;
}
.dash-prv .wx-period-dur-num { display: block; font-weight: 700; color: #035ab3; }
.dash-prv .wx-period-dur-lbl { font-size: 0.6rem; color: #5a7299; }
.dash-prv .wx-sum-attach-block { margin-bottom: 0.3rem; }
.dash-prv .wx-sum-attach-label { font-size: 0.7rem; color: #5a7299; font-weight: 700; }
.dash-prv .wx-sum-doc-link { color: #035ab3; font-weight: 600; font-size: 0.74rem; }
.dash-prv .wx-sum-doc-link .fa-file-pdf-o,
.dash-prv .doc-pdf-link .fa-file-pdf-o {
    color: #d9534f;
}
.dash-prv-exp-table-wrap {
    margin-bottom: 0;
}
.dash-prv-exp-table {
    min-width: 48rem;
    table-layout: auto;
}
.dash-prv-exp-table .dash-prv-exp-sno {
    width: 2.4rem;
}
.dash-prv-exp-table td,
.dash-prv-exp-table th {
    vertical-align: top;
}
.dash-prv-exp-table tfoot td {
    background: #f4f8fd;
    font-size: 0.82rem;
}
.dash-prv-exp-sub {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.72rem;
    font-weight: 500;
    color: #5a7299;
    line-height: 1.35;
}
.dash-prv-exp-dur {
    margin-top: 0.2rem;
    font-size: 0.74rem;
    font-weight: 700;
    color: #035ab3;
}
.dash-prv-exp-docs {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.3rem;
}
.dash-prv .board-member-qa-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.dash-prv .asp-section-title {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 700;
    color: #1a2a4a;
}
.dash-prv .asp-qa-answer.is-yes { color: #155724; font-weight: 700; }
.dash-prv .asp-qa-answer.is-no { color: #721c24; font-weight: 700; }
.dash-prv .board-member-detail-table {
    width: 100%;
    margin: 0;
}
.dash-prv .board-member-detail-table th {
    width: 28%;
    text-align: left;
    font-weight: 700;
    color: #5a7299;
    padding: 0.45rem 0.75rem 0.45rem 0;
    vertical-align: top;
}
.dash-prv .board-member-detail-table td {
    padding: 0.45rem 0;
    color: #12233f;
    font-weight: 600;
}
