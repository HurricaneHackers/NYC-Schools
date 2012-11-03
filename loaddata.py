from mongoengine import *
import xlrd

connect('doedata', host='hydr0.com') #For now we'll use hydr0, swap to heroku later?

class School(Document):
    uid = StringField()
    location = GeoPointField()
    status = StringField()

    # Stuff we might need, or maybe not
    name = StringField()
    principal_name = StringField()
    address = StringField()

    #If there was relocation
    rlocation = GeoPointField()
    rprincipal_name = StringField()
    raddresse = StringField()

geoinfo = xlrd.open_workbook('geodata.xls').sheet_by_index(0)
rlcinfo = xlrd.open_workbook('relocated.XLSX').sheet_by_index(0)
elcinfo = xlrd.open_workbook('electricity.xls').sheet_by_index(0)

hashtable = {}

for row in range(1, geoinfo.nrows): #This creates a temporary hash table
    r = geoinfo.row_values(row)
    hashtable[r[0]] = r[2], r[3]
fails1 = 0
for rid in range(2, elcinfo.nrows):
    row = elcinfo.row_values(rid)

    uid = (str(int(row[0]))+row[1])
    if len(uid) != 6:
        uid = '0'+uid
    if uid not in hashtable.keys():
        print 'There was an error loading row #%s, %s, %s' % (rid, uid, row[2])
        fails1 += 1
        continue

    q = School.objects(uid=uid)
    if len(q):
        print 'Updating row... (deleting it first)'
        q[0].delete()

    s = School(
        uid=uid,
        location=hashtable[uid],
        status='nopower',
        name=row[2],
        principal_name=row[7],
        address=row[3])
    s.save()

    print 'ADDED:', s.uid, s.location, s.name, s.address

print '\n\n\n\n\n\n\n'
fails = 0
for rid in range(1, rlcinfo.nrows): #Load relocation data
    row = rlcinfo.row_values(rid)

    uid = (str(int(row[0]))+row[1])
    if len(str(int(row[0]))) != 2:
        uid = '0'+uid
    if uid not in hashtable.keys():
        print 'There was an error loading row #%s, %s, %s' % (rid, uid, row[2])
        fails += 1
        continue
    geo = hashtable[uid]

    nuid = (str(int(row[0]))+row[8]) 
    if len(str(int(row[0]))) != 2:
        nuid = '0'+nuid
    if nuid not in hashtable.keys():
        print 'There was an error loading row #%s, (new) %s, %s' % (rid, nuid, row[4])
        fails += 1
        continue
    ngeo = hashtable[nuid]

    q = School.objects(uid=uid)
    if len(q):
        print 'Updating row... (deleting it first)'
        q[0].delete()

    s = School(
        uid=uid,
        location=ngeo,
        status='relocated',
        name=row[2],
        principal_name=row[1],
        rlocation=geo,
        rprincipal_name=row[9],
        raddresse=row[6])
    s.save()
    print 'ADDED:',s.uid, s.location, s.name, s.address

print 'F1: %s | F2: %s' % (fails1, fails)