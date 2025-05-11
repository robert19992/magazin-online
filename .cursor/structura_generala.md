STRUCTURA GENERALA MAGAZIN ONLINE

Odata ce accesam pagina principala http://localhost/ , ar trebui sa ne arate mesaj de bun venit si 2 butoane mari , login si register.

Login ne va duce pe pagina de autentificare, iar register pe pagina de creare cont nou.

In pagina de register va trebui sa avem urmatoarele campuri obligatorii pentru crearea unui cont nou, fie ca va fi client sau furnizor:
-un drop down cu tipul de cont, furnizor sau client
-nume firma
-strada
-numar strada
-CUI
-email
-parola

Dupa completarea formularului nu va fi nevoie de verificarea email-ului prin link.

In magazinul nostru online vor fi doua tipuri de conturi , furnizori si clienti. La crearea contului, fie furnizor fie client vor primi un numar unic de utilizator numit connect_id.
In functie de contul ales, dashboardul si paginile ce le poate vedea fiecare sunt descrise mai jos , Dashboard furnizor si Dashboard client.

Tot ce face magazinul nostru online este de a crea si intermedia fisiere SAP IDOC intre client si furnizor. Consideram ca toti clientii nostri folosesc SAP ca si sistem ERP sa facem situ-ul nostru sa functioneze cat mai simplu. Aceste fisiere IDOC generate de magazin vor fi stocate in doua foldere , IDOC_client si IDOC_furnizor.

Magazinul nostru nu va emite facturi sau avize de livrare direct catre client sau furnizori. Acestea vor veni din sistemele lor ERP si doar se vor stoca local pe site-ul nostru. Cu scopul de simplificare vom face un generator simplu de fisiere .txt in care vom mentiona doar produsele comandate , livrate sau facturate in urma generarii unei actiuni comanda plasata , comanda livrata si facturata.
Acest fisier text va avea titlul de confirmare comanda , aviz livrare sau factura , in functie de actiunea facuta. Sablonul pentru confirmare comanda este in folderul local documente_site/comenzi/confirmare_comanda_exemplu.txt, sablonul pentru aviz livrare este in folderul local documente_site/avize/aviz_livrare_exemplu.txt, sablonul pentru factura este documente_site/facturi/factura_exemplu.txt
Toate aceste fisiere vor fi stocate local in folderul documente_site/comenzi pentru confirmari comanda, documente_site/avize pentru aviz livrare si documente_site/facturi pentru factura.

Aceste fisiere IDOC sunt generate de magazinul nostru in urmoatoarele situatii si se stocheaza dupa cum urmeaza:
-clientul plaseaza o comanda in pagina Comanda/Cerere oferta , se va genera un IDOC cu produsele pe care le selecteaza, destinat funizorului selectat si se stocheaza in folderul IDOC_client
-furnizorul seteaza o comanda ca fiind livrata, aceasta va genera un IDOC cu informatiile ca comanda a fost livrata, acest IDOC se stocheaza in folderul IDOC_furnizor

Clientii si furnizorii se pot conecta unul cu celalalt prin pagina Connect sub forma de adaugare furnizor/client folosind numarul unic connect_id atribuit la crearea contului. Fiecare dintre ei vor trebui sa adauge fie furnizorul fie clientul in lista sa pentru a putea procesa comenzi si livrari intre ei.

Dashboard furnizor

Dashboardul furnizorului trebuie sa contina urmatoarele pagini si particularitati ale sale:

1.Pagina catalog produs
In aceasta pagina furnizorul poate vizualiza catalogul de produse sub forma de lista , poate modifica oricare dintre produse si caracteristici ale sale. Tot in aceasta pagina produsele se pot adauga manual cu toate caracteristicile respective ( cod produs, descriere, producator masina, greutate [kg], pret, stoc, data introducere). Aceasta pagina va fi o lista simpla cu toate produsele din baza de date atribuite furnizorului respectiv.
Odata introduse produsele in catalog manual de catre furnizor acestea vor avea asignat un indicator in baza de date ca apartine funizorului ce le-a introdus.

2.Import catalog produse
In aceasta pagina furnizorul poate incarca direct printr-un fisier .csv catalogul de produse cu mai multe produse. Aceste produse se adauga la lista generala de produse, in ordinea in care au fost introduse.Furnizorul le va vedea in pagina catalog produs sub o lista generala cu toate produsele. Fisierul va avea coloanele ordonate astfel sa poata coincide cu baza de date ( cod produs, descriere, producator masina, greutate [kg], pret, stoc, data introducere).

3.Arhiva documente
In aceasta pagina furnizorul poate vedea fisierele o trimise catre client, confirmarea de comanda , aviz livrare si factura. De fiecare daca cand o comanda este bifata ca fiind livrata clientul va primi o pereche de documente , aviz de livrare si factura, cu produsele din comanda livrata. Aceste documente sunt trimise in folderul documente_site/comenzi pentru confirmari comanda, documente_site/avize pentru aviz livrare si documente_site/facturi pentru factura

4. Raport vanzari
In aceasta pagina furnizorul poate vedea numarul de comenzi plasate catre el, valoare acestora si ce produse contin acele produse, cat si starea acestora, activa sau livrata.
Din aceasta pagina furnizorul poate schimba starea unei comenzi din activa in livrata(niciodata invers din livrata in activa). Odata ce ii este schimbata starea unei comenzi din activa in livrata , automat se va genera un IDOC catre client , ce se va stoca in folderul IDOC_client un fisier IDOC (cu informatii pentru sistemul ERP SAP al clientului). Tot in acelasi timp se va genera un aviz livrare cu informatiile din comanda folosind sablonul documente_site/avize/aviz_livrare_exemplu.txt , se va stoca in documente_site/avize cu un nume unic, de asemenea si o factura folosind sablonul documente_site/facturi/factura_exemplu.txt, se va stoca in documente_site/facturi cu un nume unic.


5.Connect
In aceasta pagina furnizorul poate vedea ce relatii are cu clientii sai. In sensul in care poate vedea daca este adaugat ca si furnizor. El poate vedea connect_id clientilor sai care il are adaugat si poate adauga si el clienti in aceasta sectiune. Toata aceasta sectiune este despre gestionarea clientilor si reteaua completa. Un client poate comanda la un anumit funizor doar daca il are in reteaua sa adaugat.
Funizorul poate adauga si el clientii in lista sa prin connect_id.

Dashboard Client

Dashboardul clientului trebuie sa contina urmatoarele pagini si particularitati:

1.Comanda
In aceasta pagina clientul isi poate plasa o comanda catre un furnizor selectat. Modul de operare va fi de urmatorul. Se va alege dintr-o lista drop down lista de furnizori adaugati pagina Connect prin connect_id. Clientul va introduce in campurile din pagina codul de produs din catalogul furnizorul si cantitatea solicitata. Odata plasata comanda prin butonul comanda sistemul va trimite comanda la furnizor sub forma de IDOC, ce va fi stocat in folderul local IDOC_funizor iar confirmarea de comanda generata si stocata locala in folderul documente_site/comenzi folosind sablonul pentru confirmarea de comanda documente_site/comenzi/confirmare_comanda_exemplu.txt , cu nume unic.

2. Comenzi active
In aceasta pagina clientul isi poate urmarii comenzile trimise catre furnizorii sai, daca sunt livrate sau nu. Comenzile active vor fi de forma unei liste cu toate informatiile disponibile afisate cat si valoarea totala a acestor comenzi.

3. Raport comenzi
In aceasta pagina clientul isi poate vedea o statistica a tuturor comenzilor cat si valoare totala , tipul de produse , cantitati si livrate/nelivrate.

4.Arhiva documente
In aceasta pagina clientul isi poate vedea sub forma unei liste documentele primite in urma livrarii de catre funizor a uneia dintr comenzi.De asemenea va putea vedea archiva de documente primite confirmare comanda , aviz livrare si factura. De fapt el va putea vedea continurul folderul documente_site/comenzi pentru confirmari comanda, documente_site/avize pentru aviz livrare si documente_site/facturi pentru factura.

5.Connect
In aceasta pagina clientul isi poate gestiona furnizorii cu care a format o relatie prin adaugarea connect_id unic de funizor. Acesta poate avea furnizori multiplii si le poate vedea toate detaliile cum ar fi denumirea si connect_id.